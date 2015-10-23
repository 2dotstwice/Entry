<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\Guzzle\OAuthProtectedService;
use CultuurNet\Auth\TokenCredentials;
use Guzzle\Http\Client;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Url;

class EntryAPI extends OAuthProtectedService
{
    const TRANSLATION_MODIFIED = 'TranslationModified';

    const TRANSLATION_CREATED = 'TranslationCreated';

    const KEYWORD_WITHDRAWN = 'KeywordWithdrawn';

    const KEYWORD_PRIVATE = 'PrivateKeyword';

    const KEYWORDS_CREATED = 'KeywordsCreated';

    const PRIVATE_KEYWORD = 'PrivateKeyword';

    const ITEM_CREATED = 'ItemCreated';

    const ITEM_WITHDRAWN = 'ItemWithdrawn';

    /**
     * Status code when an item has been succesfully updated.
     * @var string
     */
    const ITEM_MODIFIED = 'ItemModified';

    /**
     * Status code when a value of an item has been removed.
     */
    const VALUE_WITHDRAWN  = 'ValueWithdrawn';

    const NOT_FOUND = 'NotFound';

    private $cdb_schema_version = '3.3';

  /**
     * @param string $baseUrl
     * @param ConsumerCredentials $consumer
     * @param TokenCredentials $tokenCredentials
     * @param string $cdb_schema_version
     */
    public function __construct(
        $baseUrl,
        ConsumerCredentials $consumerCredentials,
        TokenCredentials $tokenCredentials = null,
        $cdb_schema_version = '3.3'
    ) {
        parent::__construct(
            $baseUrl,
            $consumerCredentials,
            $tokenCredentials
        );

        $this->cdb_schema_version = $cdb_schema_version;
    }

    protected function eventTranslationPath($eventId)
    {
        return "event/{$eventId}/translations";
    }

    protected function eventKeywordsPath($eventId)
    {
        return "event/{$eventId}/keywords";
    }

    protected function updatePath($entityId, EntityType $entityType)
    {
        return $entityType . "/" . $entityId;
    }

    /**
     * @return Client
     */
    protected function getClient(array $additionalOAuthParameters = array())
    {
        $client = parent::getClient($additionalOAuthParameters);

        $client->setDefaultOption(
            'headers',
            [
                'Accept' => 'text/xml'
            ]
        );

        return $client;
    }


    /**
     * @param string $eventId
     * @param Language $language
     * @param string $title
     *
     * @return Rsp
     */
    public function translateEventTitle($eventId, Language $language, $title)
    {
        return $this->translate($eventId, $language, ['title' => $title]);
    }

    /**
     * @param string $eventId
     * @param Language $language
     * @param string $description
     *
     * @return Rsp
     */
    public function translateEventDescription(
        $eventId,
        Language $language,
        $description
    ) {
        return $this->translate(
            $eventId,
            $language,
            [
                'longdescription' => $description,
                'shortdescription' => iconv_substr($description, 0, 400),
            ]
        );
    }

    private function translate($eventId, Language $language, $fields)
    {
        $request = $this->getClient()->post(
            $this->eventTranslationPath($eventId),
            null,
            [
                'lang' => (string)$language,
            ] + $fields
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardTranslationResponseIsSuccessful($rsp);

        return $rsp;
    }

    public function addKeyword($eventId, Keyword $keyword)
    {
        $request = $this->getClient()->post(
            $this->eventKeywordsPath($eventId),
            null,
            [
                'keywords' => (string)$keyword,
            ]
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardKeywordResponseIsSuccessful($rsp);

        return $rsp;
    }

    public function guardKeywordResponseIsSuccessful(Rsp $rsp)
    {
        if ($rsp->getCode() === self::PRIVATE_KEYWORD) {
            throw new PrivateKeywordException($rsp);
        } elseif ($rsp->getCode() !== self::KEYWORDS_CREATED) {
            throw new UnexpectedKeywordErrorException($rsp);
        }
    }

    /**
     * @param string $eventId
     * @param Keyword $keyword
     * @return Rsp
     * @throws UnexpectedKeywordDeleteErrorException
     */
    public function deleteKeyword($eventId, Keyword $keyword)
    {
        /** @var EntityEnclosingRequest $request */
        $request = $this->getClient()->delete(
            $this->eventKeywordsPath($eventId)
        );

        $request->getQuery()->add('keyword', (string)$keyword);

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardDeleteKeywordResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Update the title for an event.
     *
     * @param string $entityId
     * @param EntityType $entityType
     * @param Title $title
     * @param Language $language
     * @return Rsp
     *
     * @throws UpdateEventErrorException
     */
    public function updateTitle($entityId, EntityType $entityType, Title $title, Language $language)
    {
        $request = $this->getClient()->post(
            $this->updatePath($entityId, $entityType) . '/title',
            null,
            [
                'lang' => (string) $language,
                'value' => (string) $title,
            ]
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Update the description of an event.
     *
     * @param string $entityId
     * @param EntityType $entityType
     * @param String $description
     * @param Language $language
     * @return Rsp
     *
     * @throws UpdateEventErrorException
     */
    public function updateDescription($entityId, EntityType $entityType, String $description, Language $language)
    {
        $request = $this->getClient()->post(
            $this->updatePath($entityId, $entityType) . '/description',
            null,
            [
                'lang' => (string) $language,
                'value' => (string) $description,
            ]
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Delete the description of an item.
     *
     * @param string $entityId
     * @param EntityType $entityType
     * @param Language $language
     * @return Rsp
     *
     * @throws UpdateEventErrorException
     */
    public function deleteDescription($entityId, EntityType $entityType, Language $language)
    {
        $request = $this->getClient()->delete(
            $this->updatePath($entityId, $entityType) . '/description',
            null,
            [
                'lang' => (string) $language,
            ]
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardValueWithdrawnResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Update the required age for an event.
     *
     * @param string $entityId
     * @param EntityType $entityType
     * @param String $description
     * @param Language $language
     * @return Rsp
     *
     * @throws UpdateEventErrorException
     */
    public function updateAge($entityId, EntityType $entityType, Number $age)
    {
        $request = $this->getClient()->post(
            $this->updatePath($entityId, $entityType) . '/age',
            null,
            [
                'value' => (int) $age->getNumber(),
            ]
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Delete the required age for an event.
     *
     * @param string $entityId
     * @param EntityType $entityType
     * @return Rsp
     *
     * @throws UpdateEventErrorException
     */
    public function deleteAge($entityId, EntityType $entityType)
    {
        $request = $this->getClient()->delete(
            $this->updatePath($entityId, $entityType) . '/age',
            null,
            []
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardValueWithdrawnResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Update the organiser of an event.
     *
     * @param string $entityId
     * @param EntityType $entityType
     * @param String $organiser
     * @return Rsp
     *
     * @throws UpdateEventErrorException
     */
    public function updateOrganiser($entityId, EntityType $entityType, String $organiser)
    {
        $request = $this->getClient()->post(
            $this->updatePath($entityId, $entityType) . '/organiser',
            null,
            [
                'value' => (string) $organiser,
            ]
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Delete the organiser of an event.
     *
     * @param string $entityId
     * @param EntityType $entityType
     * @return Rsp
     *
     * @throws UpdateEventErrorException
     */
    public function deleteOrganiser($entityId, EntityType $entityType)
    {
        $request = $this->getClient()->delete(
            $this->updatePath($entityId, $entityType) . '/organiser',
            null,
            []
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardValueWithdrawnResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Update the facilities for an event.
     *
     * @param type $entityId
     * @param \DOMDocument $dom
     */
    public function updateFacilities($entityId, \DOMDocument $dom)
    {

        $entityType = new EntityType('event');

        $request = $this->getClient()->post(
            $this->updatePath($entityId, $entityType) . '/updateFacilities',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $dom->saveXML()
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;

    }

    /**
     * Update the contact info for an item.
     *
     * @param stro,g $entityId
     * @param EntityType $entityType
     * @param \CultureFeed_Cdb_Data_ContactInfo $contactInfo
     */
    public function updateContactInfo($entityId, EntityType $entityType, \CultureFeed_Cdb_Data_ContactInfo $contactInfo)
    {

        $dom = new \DOMDocument('1.0', 'utf-8');
        $domElement = $dom->createElement('info');
        $contactInfo->appendToDOM($domElement);

        $contactInfoNode = $dom->importNode($domElement->childNodes->item(0), true);
        $dom->appendChild($contactInfoNode);

        $request = $this->getClient()->post(
            $this->updatePath($entityId, $entityType) . '/updateContactinfo',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $dom->saveXml()
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;

    }

    /**
     * Update the bookingperiod for given item.
     *
     * @param string $entityId
     * @param BookingPeriod $bookingPeriod
     */
    public function updateBookingPeriod($entityId, BookingPeriod $bookingPeriod)
    {

        $entityType = new EntityType('event');
        $request = $this->getClient()->post(
            $this->updatePath($entityId, $entityType) . '/bookingperiod',
            null,
            [
                'startdate' => $bookingPeriod->getStartDate(),
                'enddate' => $bookingPeriod->getEndDate(),
            ]
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;

    }

    /**
     * Check the permission of a user to edit one or more items.
     *
     * @param string $userid
     *   User id of the user.
     * @param string $email
     *   Email address of that user
     * @param Array $ids
     *   Array of ids to check.
     * @return Rsp
     *
     * @throws CultureFeed_ParseException
     */
    public function checkPermission($userid, $email, $ids)
    {

        $request = $this->getClient()->get(
            'event/checkpermission',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            )
        );

        $query = $request->getQuery();
        $query->add('user', $userid);
        $query->add('email', $email);
        $query->add('ids', $ids);

        $response = $request->send();

        $result = $response->getBody(true);

        try {
            return new \CultureFeed_SimpleXMLElement($result);
        } catch (Exception $e) {
            throw new \CultureFeed_ParseException($result);
        }
    }

    private function guardDeleteKeywordResponseIsSuccessful(Rsp $rsp)
    {
        if ($rsp->getCode() !== self::KEYWORD_WITHDRAWN) {
            throw new UnexpectedKeywordDeleteErrorException($rsp);
        }
    }

    private function guardTranslationResponseIsSuccessful(Rsp $rsp)
    {
        $validCodes = [
            self::TRANSLATION_CREATED,
            self::TRANSLATION_MODIFIED
        ];
        if (!in_array($rsp->getCode(), $validCodes)) {
            throw new UnexpectedTranslationErrorException($rsp);
        }
    }

    /**
     * Get an event.
     *
     * @param string $id
     *
     * @return \CultureFeed_Cdb_Item_Event
     *
     * @throws \CultureFeed_Cdb_ParseException
     * @throws \CultureFeed_ParseException
     */
    public function getEvent($id)
    {
        $request = $this->getClient()->get(
            'event/' . $id,
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            )
        );

        $response = $request->send();

        $result = $response->getBody(true);

        try {
            $xml = new \SimpleXMLElement($result);
        } catch (Exception $e) {
            throw new \CultureFeed_ParseException($result);
        }

        if ($xml->event) {
            $eventXml = $xml->event;
            return \CultureFeed_Cdb_Item_Event::parseFromCdbXml($eventXml);
        }

        throw new \CultureFeed_ParseException($result);

    }

    /**
     * Create an event
     *
     * @param \CultureFeed_Cdb_Item_Event $event
     *
     * @return string The cdbid of the created event.
     */
    public function createEvent(\CultureFeed_Cdb_Item_Event $event)
    {
        return $this->createEventFromRawXml($this->getCdbXml($event));
    }

    /**
     * @param string $xml
     *
     * @return string The cdbid of the created event.
     */
    public function createEventFromRawXml($xml)
    {
        $request = $this->getClient()->post(
            'event',
            array(
                'Content-Type' => 'application/xml',
            ),
            $xml
        );

        $response = $request->send();
        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemCreateResponseIsSuccessful($rsp);

        $linkParts = explode('/', $rsp->getLink());
        $eventId = array_pop($linkParts);

        return $eventId;
    }

    /**
     * Update an event.
     *
     * @param CultureFeed_Cdb_Item_Event $event
     *   The event to update.
     */
    public function updateEvent(\CultureFeed_Cdb_Item_Event $event)
    {
        $request = $this->getClient()->post(
            'event/' . $event->getCdbId(),
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $this->getCdbXml($event)
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;

    }

    /**
     * Updating an event from raw xml.
     *
     * @param string $eventId
     * @param string $xml
     * @return static
     * @throws UpdateEventErrorException
     */
    public function updateEventFromRawXml($eventId, $xml)
    {
        $request = $this->getClient()->put(
            'event/' . $eventId,
            array(
                'Content-Type' => 'application/xml',
            ),
            $xml
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * Delete an event.
     *
     * @param string $id
     *   ID of the event to delete.
     */
    public function deleteEvent($id)
    {
        $request = $this->getClient()->delete(
            'event/' . $id,
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            )
        );

        $response = $request->send();
        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemWithdrawnResponseIsSuccessful($rsp);

        return $rsp;

    }

    /**
     * Create an actor in UDB2.
     *
     * @param CultureFeed_Cdb_Item_Actor $actor
     *   The actor to create.
     * @return string
     *   The cdbid of the created actor.
     */
    public function createActor(\CultureFeed_Cdb_Item_Actor $actor)
    {

        $request = $this->getClient()->post(
            'actor',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $this->getCdbXml($actor)
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemCreateResponseIsSuccessful($rsp);

        $linkParts = explode('/', $rsp->getLink());
        $eventId = array_pop($linkParts);

        return $eventId;
    }

    /**
     * Update an actor in UDB2.
     *
     * @param CultureFeed_Cdb_Item_Actor $actor
     *   The actor to update.
     * @return string
     *   The cdbid of the created actor.
     */
    public function updateActor(\CultureFeed_Cdb_Item_Actor $actor)
    {

        $request = $this->getClient()->post(
            'actor',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $this->getCdbXml($actor)
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        $linkParts = explode('/', $rsp->getLink());
        $eventId = array_pop($linkParts);

        return $eventId;
    }

    private function guardItemCreateResponseIsSuccessful(Rsp $rsp)
    {
        if ($rsp->getLevel() == $rsp::LEVEL_ERROR) {
            // @todo: use a more specific exception
            throw new CreateEventErrorException($rsp);
        }
    }

    private function guardItemUpdateResponseIsSuccessful(Rsp $rsp)
    {
        $validCodes = [
            self::ITEM_MODIFIED,
        ];
        if (!in_array($rsp->getCode(), $validCodes)) {
            throw new UpdateEventErrorException($rsp);
        }
    }

    private function guardItemWithdrawnResponseIsSuccessful(Rsp $rsp)
    {
        $validCodes = [
            self::ITEM_WITHDRAWN,
        ];
        if (!in_array($rsp->getCode(), $validCodes)) {
            throw new UpdateEventErrorException($rsp);
        }
    }

    private function guardValueWithdrawnResponseIsSuccessful(Rsp $rsp)
    {
        $validCodes = [
            self::VALUE_WITHDRAWN,
        ];
        if (!in_array($rsp->getCode(), $validCodes)) {
            throw new UpdateEventErrorException($rsp);
        }
    }

    /**
     * Get Cdb XML string
     *
     * @param CultureFeed_Cdb_Item_Base $item
     *
     * @return string
     * @throws \Exception
     */
    private function getCdbXml(\CultureFeed_Cdb_Item_Base $item)
    {
        $cdb = new \CultureFeed_Cdb_Default($this->cdb_schema_version);
        $cdb->addItem($item);

        return (string) $cdb;
    }
}
