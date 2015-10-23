<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use CultureFeed_Cdb_Data_Address;
use CultureFeed_Cdb_Data_Address_PhysicalAddress;
use CultureFeed_Cdb_Data_Calendar_Timestamp;
use CultureFeed_Cdb_Data_Calendar_TimestampList;
use CultureFeed_Cdb_Data_Category;
use CultureFeed_Cdb_Data_CategoryList;
use CultureFeed_Cdb_Data_ContactInfo;
use CultureFeed_Cdb_Data_EventDetail;
use CultureFeed_Cdb_Data_EventDetailList;
use CultureFeed_Cdb_Data_File;
use CultureFeed_Cdb_Data_Language;
use CultureFeed_Cdb_Data_LanguageList;
use CultureFeed_Cdb_Data_Location;
use CultureFeed_Cdb_Data_Mail;
use CultureFeed_Cdb_Data_Organiser;
use CultureFeed_Cdb_Data_Performer;
use CultureFeed_Cdb_Data_PerformerList;
use CultureFeed_Cdb_Data_Phone;
use CultureFeed_Cdb_Data_Price;
use CultureFeed_Cdb_Data_Url;
use CultureFeed_Cdb_Item_Event;
use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\Guzzle\DefaultHttpClientFactory;
use CultuurNet\Auth\Guzzle\HttpClientFactory;
use CultuurNet\Auth\TokenCredentials;
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
            ),
            new TokenCredentials(
                'tokenkey',
                'tokensecret'
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
            'application/x-www-form-urlencoded',
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

    /**
     * @test
     */
    public function it_can_create_an_event_from_cdb_item_event()
    {
        $response = (new Response(200))->setBody(
            file_get_contents(__DIR__ . '/samples/ItemCreated.xml')
        );
        $this->mockPlugin->addResponse($response);

        $xml = file_get_contents(__DIR__ . '/samples/valid_event.xml');
        $event = $this->createCulturefeedItemEvent("004aea08-e13d-48c9-b9eb-a18f20e6d44e");

        $eventid = $this->entryAPI->createEvent(
            $event
        );

        $requests = $this->mockPlugin->getReceivedRequests();

        /** @var RequestInterface|MessageInterface|EntityEnclosingRequestInterface $request */
        $request = reset($requests);

        $this->assertEquals(
            'POST',
            $request->getMethod()
        );

        $this->assertEquals(
            'http://example.com/event',
            $request->getUrl()
        );

        $this->assertEquals(
            'application/xml',
            (string)$request->getHeader('Content-Type')
        );

        $xml = file_get_contents(__DIR__ . '/samples/event_from_culturefeed_item_event.xml');
        $this->assertXmlStringEqualsXmlString(
            $xml,
            (string)$request->getBody()
        );

        $expectedEventId = "004aea08-e13d-48c9-b9eb-a18f20e6d44e";

        $this->assertEquals(
            $expectedEventId,
            $eventid
        );
    }

    /**
     * @test
     */
    public function it_can_create_an_event_from_raw_xml()
    {
        $response = (new Response(200))->setBody(
            file_get_contents(__DIR__ . '/samples/ItemCreated.xml')
        );
        $this->mockPlugin->addResponse($response);

        $xml = file_get_contents(__DIR__ . '/samples/valid_event.xml');

        $eventid = $this->entryAPI->createEventFromRawXml(
            $xml
        );

        $requests = $this->mockPlugin->getReceivedRequests();

        /** @var RequestInterface|MessageInterface|EntityEnclosingRequestInterface $request */
        $request = reset($requests);

        $this->assertEquals(
            'POST',
            $request->getMethod()
        );

        $this->assertEquals(
            'http://example.com/event',
            $request->getUrl()
        );

        $this->assertEquals(
            'application/xml',
            (string)$request->getHeader('Content-Type')
        );

        $this->assertEquals(
            $xml,
            (string)$request->getBody()
        );

        $expectedEventId = "004aea08-e13d-48c9-b9eb-a18f20e6d44e";

        $this->assertEquals(
            $expectedEventId,
            $eventid
        );
    }

    /**
     * @test
     */
    public function it_can_update_an_event_from_raw_xml()
    {
        $response = (new Response(200))->setBody(
            file_get_contents(__DIR__ . '/samples/ItemModifiedEntryAPI.xml')
        );
        $this->mockPlugin->addResponse($response);

        $xml = file_get_contents(__DIR__ . '/samples/valid_event.xml');

        $eventId = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $rsp = $this->entryAPI->updateEventFromRawXml($eventId, $xml);

        $requests = $this->mockPlugin->getReceivedRequests();

        /** @var RequestInterface|MessageInterface|EntityEnclosingRequestInterface $request */
        $request = reset($requests);

        $this->assertEquals(
            'PUT',
            $request->getMethod()
        );

        $this->assertEquals(
            'http://example.com/event/004aea08-e13d-48c9-b9eb-a18f20e6d44e',
            $request->getUrl()
        );

        $this->assertEquals(
            'application/xml',
            (string)$request->getHeader('Content-Type')
        );

        $this->assertEquals(
            $xml,
            (string)$request->getBody()
        );

        $expectedRsp = new Rsp(
            '0.1',
            Rsp::LEVEL_INFO,
            'ItemModified',
            'http://test.rest.uitdatabank.be/api/v3/event/004aea08-e13d-48c9-b9eb-a18f20e6d44e',
            ''
        );

        $this->assertEquals(
            $expectedRsp,
            $rsp
        );
    }

    public function createCulturefeedItemEvent($cdbid)
    {
        $event = new CultureFeed_Cdb_Item_Event();
        $event->setAvailableFrom('2010-02-25T00:00:00');
        $event->setAvailableTo('2010-08-09T00:00:00');
        $event->setCdbId($cdbid);
        $event->setCreatedBy('mverdoodt');
        $event->setCreationDate('2010-07-05T18:28:18');
        $event->setExternalId('SKB Import:SKB00001_216413');
        $event->setIsParent(false);
        $event->setLastUpdated('2010-07-28T13:58:55');
        $event->setLastUpdatedBy('mverdoodt');
        $event->setOwner('SKB Import');
        $event->setPctComplete(80);
        $event->setPublished(true);
        $event->setValidator('SKB');
        $event->setWfStatus('approved');
        $event->setAgeFrom(18);
        $event->setPrivate(false);

        $calendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
        $calendar->add(new CultureFeed_Cdb_Data_Calendar_Timestamp('2010-08-01', '21:00:00.0000000'));
        $event->setCalendar($calendar);

        $categories = new CultureFeed_Cdb_Data_CategoryList();
        $categories->add(
            new CultureFeed_Cdb_Data_Category(
                CultureFeed_Cdb_Data_Category::CATEGORY_TYPE_EVENT_TYPE,
                '0.50.4.0.0',
                'Concert'
            )
        );
        $categories->add(
            new CultureFeed_Cdb_Data_Category(
                CultureFeed_Cdb_Data_Category::CATEGORY_TYPE_THEME,
                '1.8.2.0.0',
                'Jazz en blues'
            )
        );
        $categories->add(
            new CultureFeed_Cdb_Data_Category(
                CultureFeed_Cdb_Data_Category::CATEGORY_TYPE_PUBLICSCOPE,
                '6.2.0.0.0',
                'Regionaal'
            )
        );
        $event->setCategories($categories);

        $contactInfo = new CultureFeed_Cdb_Data_ContactInfo();
        $contactInfo->addMail(new CultureFeed_Cdb_Data_Mail('info@bonnefooi.be', null, null));
        $contactInfo->addPhone(new CultureFeed_Cdb_Data_Phone('0487-62.22.31'));
        $url = new CultureFeed_Cdb_Data_Url('http://www.bonnefooi.be');
        $url->setMain();
        $contactInfo->addUrl($url);
        $event->setContactInfo($contactInfo);

        $details = new CultureFeed_Cdb_Data_EventDetailList();

        $detailNl = new CultureFeed_Cdb_Data_EventDetail();
        $detailNl->setLanguage('nl');
        $detailNl->setTitle('The Bonnefooi Acoustic Jam');
        $detailNl->setCalendarSummary('zo 01/08/10 om 21:00');

        $performers = new CultureFeed_Cdb_Data_PerformerList();
        $performers->add(new CultureFeed_Cdb_Data_Performer('Muzikant', 'Matt, the Englishman in Brussels'));
        $detailNl->setPerformers($performers);

        $detailNl->setLongDescription('Weggelaten voor leesbaarheid...');

        $file = new CultureFeed_Cdb_Data_File();
        $file->setMain();
        $file->setCopyright('Bonnefooi');
        $file->setHLink('http://www.bonnefooi.be/images/sized/site/images/uploads/Jeroen_Jamming-453x604.jpg');
        $file->setMediaType(CultureFeed_Cdb_Data_File::MEDIA_TYPE_IMAGEWEB);
        $file->setTitle('Jeroen Jamming');

        $detailNl->getMedia()->add($file);

        $price = new CultureFeed_Cdb_Data_Price(0);
        $price->setTitle('The Bonnefooi Acoustic Jam');
        $detailNl->setPrice($price);

        $detailNl->setShortDescription('Korte omschrijving.');

        $details->add($detailNl);

        $detailEn = new CultureFeed_Cdb_Data_EventDetail();
        $detailEn->setLanguage('en');
        $detailEn->setShortDescription('Short description.');
        $details->add($detailEn);

        $event->setDetails($details);

        $event->addKeyword('Free Jazz, Acoustisch');

        $address = new CultureFeed_Cdb_Data_Address();
        $physicalAddress = new CultureFeed_Cdb_Data_Address_PhysicalAddress();
        $physicalAddress->setCity('Brussel');
        $physicalAddress->setCountry('BE');
        $physicalAddress->setHouseNumber(8);
        $physicalAddress->setStreet('Steenstraat');
        $physicalAddress->setZip(1000);
        $address->setPhysicalAddress($physicalAddress);

        $location = new CultureFeed_Cdb_Data_Location($address);

        $location->setLabel('Café Bonnefooi');
        $location->setCdbid('920e9755-94a0-42c1-8c8c-9d17f693d0be');
        $event->setLocation($location);

        $organiser = new CultureFeed_Cdb_Data_Organiser();
        $organiser->setLabel('Café Bonnefooi');
        $event->setOrganiser($organiser);

        $languages = new CultureFeed_Cdb_Data_LanguageList();
        $languages->add(new CultureFeed_Cdb_Data_Language('Nederlands', CultureFeed_Cdb_Data_Language::TYPE_SPOKEN));
        $languages->add(new CultureFeed_Cdb_Data_Language('Frans', CultureFeed_Cdb_Data_Language::TYPE_SPOKEN));
        $event->setLanguages($languages);

        return $event;
    }
}
