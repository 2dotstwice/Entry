<?php
/**
 * @file
 * EntityType tests.
 */

namespace CultuurNet\Entry;


class EntityTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_allows_a_valid_type()
    {
        $type = new EntityType('production');

        $this->assertEquals('production', $type->getType());
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_refuses_an_invalid_type() {
        new EntityType('eevent');
    }
}
