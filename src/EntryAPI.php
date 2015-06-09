<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use CultuurNet\Auth\Guzzle\OAuthProtectedService;
use Guzzle\Http\Message\EntityEnclosingRequest;

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

    const NOT_FOUND = 'NotFound';

    protected function eventTranslationPath($eventId)
    {
        return "event/{$eventId}/translations";
    }

    protected function eventKeywordsPath($eventId)
    {
        return "event/{$eventId}/keywords";
    }

    /**
     * @return \Guzzle\Http\Client
     */
    protected function getClient()
    {
        $client = parent::getClient();

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
        }
        catch (Exception $e) {
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
        }
        catch (Exception $e) {
          throw new \CultureFeed_ParseException($result);
        }

        if ($xml->event) {
          $eventXml = $xml->event;
          return \CultureFeed_Cdb_Item_Event::parseFromCdbXml($eventXml);
        }

        throw new \CultureFeed_ParseException($result);

    }

    /**
     * Delete an event.
     *
     * @param string $id
     *   ID of the event to delete.
     */
    public function deleteEvent($id) {

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
     * Create an event
     *
     * @param \CultureFeed_Cdb_Item_Event $event
     *
     * @return string The cdbid of the created event.
     */
    public function createEvent(\CultureFeed_Cdb_Item_Event $event)
    {
        $request = $this->getClient()->post(
            'event',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $this->getEventXml($event)
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
    public function updateEvent(\CultureFeed_Cdb_Item_Event $event) {
        $cdb = new \CultureFeed_Cdb_Default();
        $cdb->addItem($event);
        $cdbXml = (string) $cdb;

        $request = $this->getClient()->post(
            'event/' . $event->getCdbId(),
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $cdbXml
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardItemUpdateResponseIsSuccessful($rsp);

        return $rsp;

    }

    /**
     * Create an actor in UDB2.
     *
     * @param string $cdbxml
     *   Actor cdbxml.
     * @return string
     *   The cdbid of the created actor.
     */
    public function createActor($cdbxml)
    {

        $request = $this->getClient()->post(
            'actor',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $cdbxml
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
     * @param string $cdbxml
     *   Actor cdbxml.
     * @return string
     *   The cdbid of the created actor.
     */
    public function updateActor($cdbxml)
    {

        $request = $this->getClient()->post(
            'actor',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $cdbxml
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

    /**
     * Get event XML string
     *
     * @param CultureFeed_Cdb_Item_Event $event
     *
     * @return string
     * @throws \Exception
     */
    private function getEventXml(\CultureFeed_Cdb_Item_Event $event)
    {
        $cdb = new \CultureFeed_Cdb_Default();
        $cdb->addItem($event);

        return (string) $cdb;
    }
}
