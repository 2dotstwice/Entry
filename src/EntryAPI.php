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
     * @param string $cdbxml
     *   Event cdbxml.
     * @return string
     *   The cdbid of the created event.
     */
    public function createEvent($cdbxml)
    {
        $request = $this->getClient()->post(
            'event',
            array(
                'Content-Type' => 'application/xml; charset=UTF-8',
            ),
            $cdbxml
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardEventCreateResponseIsSuccessful($rsp);

        $linkParts = explode('/', $rsp->getLink());
        $eventId = array_pop($linkParts);

        return $eventId;
    }

    private function guardEventCreateResponseIsSuccessful(Rsp $rsp)
    {
        if ($rsp->getLevel() == $rsp::LEVEL_ERROR) {
            // @todo: use a more specific exception
            throw new CreateEventErrorException($rsp);
        }
    }
}
