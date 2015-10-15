<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

class RspTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_a_http_xml_response()
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
<rsp version="2.0" level="INFO">
  <code>TranslationModified</code>
  <link>http://rest.uitdatabank.be/api/v2/event/ebc9eb48-da7a-4e94-8960-be2fb2a696f3</link>
</rsp>';

        $rsp = Rsp::fromResponseBody($xml);

        $this->assertEquals(
            'TranslationModified',
            $rsp->getCode()
        );

        $this->assertEquals(Rsp::LEVEL_INFO, $rsp->getLevel());
        $this->assertEquals('http://rest.uitdatabank.be/api/v2/event/ebc9eb48-da7a-4e94-8960-be2fb2a696f3', $rsp->getLink());
        $this->assertEquals('2.0', $rsp->getVersion());
    }

    /**
     * @test
     */
    public function it_can_be_created_with_a_factory_for_errors()
    {
        $rsp = Rsp::error('INVALID_DATE', 'Invalid date detected');
        $expectedRsp = new Rsp(
            '0.1',
            Rsp::LEVEL_ERROR,
            'INVALID_DATE',
            null,
            'Invalid date detected'
        );

        $this->assertEquals(
            $expectedRsp,
            $rsp
        );
    }

    /**
     * @test
     */
    public function it_can_tell_if_it_is_an_error()
    {
        $rsp = new Rsp(
            '0.1',
            Rsp::LEVEL_ERROR,
            'INVALID_DATE',
            null,
            'Invalid date detected'
        );

        $this->assertTrue($rsp->isError());

        $rsp = new Rsp(
            '0.1',
            Rsp::LEVEL_INFO,
            'ItemCreated',
            null,
            'Item was created'
        );

        $this->assertFalse($rsp->isError());
    }

    /**
     * @test
     */
    public function it_can_be_serialized_to_xml()
    {
        $rsp = new Rsp(
            '0.1',
            Rsp::LEVEL_INFO,
            'ItemCreated',
            'http://www.uitdatabank.be/api/v3/event/004aea08-e13d-48c9-b9eb-a18f20e6d44e',
            null
        );

        $this->assertXmlStringEqualsXmlFile(
            __DIR__ . '/samples/ItemCreated.xml',
            $rsp->toXml()
        );
    }
}
