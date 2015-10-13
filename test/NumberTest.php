<?php
/**
 * @file
 * Number tests.
 */

namespace CultuurNet\Entry;

class NumberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_refuses_a_string()
    {
        new Number('number');
    }

    /**
     * @test
     */
    public function it_can_be_cast_to_int()
    {
        $number = new Number(5);
        $this->assertEquals(5, $number->getNumber());
    }

    /**
     * @test
     */
    public function it_can_be_serialized_to_a_json_string()
    {
        $number = new Number(5);
        $serializedNumber = json_encode($number);
        $this->assertEquals(5, $serializedNumber);
    }
}
