<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

class EventPermissionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_requires_cdbid_to_be_a_string()
    {
        new EventPermission('1', true);
        $this->setExpectedException(\InvalidArgumentException::class);
        new EventPermission(1, true);
    }

    /**
     * @test
     */
    public function it_requires_editable_to_be_a_boolean()
    {
        new EventPermission('1', true);
        $this->setExpectedException(\InvalidArgumentException::class);
        new EventPermission('1', 'true');
    }

    /**
     * @test
     */
    public function it_can_return_its_cdbid()
    {
        $eventPermission = new EventPermission('123', true);
        $this->assertEquals('123', $eventPermission->getCdbid());

        $eventPermission = new EventPermission('456', false);
        $this->assertEquals('456', $eventPermission->getCdbid());
    }

    /**
     * @test
     */
    public function it_can_tell_if_the_event_editable()
    {
        $eventPermission = new EventPermission('123', true);
        $this->assertTrue($eventPermission->isEditable());

        $eventPermission = new EventPermission('456', false);
        $this->assertFalse($eventPermission->isEditable());
    }
}
