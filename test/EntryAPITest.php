<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\Guzzle\DefaultHttpClientFactory;
use CultuurNet\Auth\Guzzle\HttpClientFactory;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Guzzle\Http\Message\MessageInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use GuzzleHttp\Message\RequestInterface;

class EntryAPITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockPlugin
     */
    private $mockPlugin;

    /**
     * @var EntryAPI
     */
    private $entryAPI;

    public function setUp()
    {
        $this->entryAPI = new EntryAPI(
            'http://example.com',
            new ConsumerCredentials(
                'key',
                'secret'
            )
        );

        $this->mockPlugin = new MockPlugin();

        $clientFactory = new DefaultHttpClientFactory();
        $clientFactory->addSubscriber($this->mockPlugin);

        $this->entryAPI->setHttpClientFactory($clientFactory);
    }

    /**
     * @test
     */
    public function it_can_update_descriptions()
    {
        $response = (new Response(200))->setBody(
            file_get_contents(__DIR__ . '/samples/ItemModified.xml')
        );
        $this->mockPlugin->addResponse($response);

        $rsp = $this->entryAPI->updateDescription(
            'foo',
            new EntityType('event'),
            new String('Félixeën is een leuk monument! Blablabla.'),
            new Language('nl')
        );

        $requests = $this->mockPlugin->getReceivedRequests();

        /** @var RequestInterface|MessageInterface|EntityEnclosingRequestInterface $request */
        $request = reset($requests);

        $this->assertEquals(
            'POST',
            $request->getMethod()
        );

        $this->assertEquals(
            'http://example.com/event/foo/description',
            $request->getUrl()
        );

        $this->assertEquals(
            'application/x-www-form-urlencoded; charset=utf-8',
            (string)$request->getHeader('Content-Type')
        );

        $this->assertEquals(
            'lang=nl&value=F%C3%A9lixe%C3%ABn%20is%20een%20leuk%20monument%21%20Blablabla.',
            (string)$request->getPostFields()
        );

        $expectedRsp = new Rsp(
            '3.0',
            Rsp::LEVEL_INFO,
            'ItemModified',
            'http://test.rest.uitdatabank.be/api/v3/event/5fad63c2-0b0a-49b3-99ae-3a12427d5f51',
            ''
        );

        $this->assertEquals(
            $expectedRsp,
            $rsp
        );
    }
}
