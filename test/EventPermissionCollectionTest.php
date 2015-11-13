<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

class EventPermissionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_only_accepts_instances_of_EventPermission()
    {
        $a = new EventPermission('123', true);
        $b = new EventPermission('456', false);

        new EventPermissionCollection([$a, $b]);

        $this->setExpectedException(\InvalidArgumentException::class);

        new EventPermissionCollection([$a, $b, new \stdClass()]);
    }

    /**
     * @test
     */
    public function it_can_serialize_to_xml()
    {
        $a = new EventPermission('123', true);
        $b = new EventPermission('456', false);

        $collection = new EventPermissionCollection([$a, $b]);

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/samples/event_permissions.xml', $collection->toXml());
    }
}
