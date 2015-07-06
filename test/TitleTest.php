<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

class TitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_requires_a_string_value()
    {
        $this->setExpectedException('\\InvalidArgumentException');
        $string = new Title(false);
    }

    /**
     * @test
     */
    public function it_needs_to_be_at_least_one_character_long()
    {
        $this->setExpectedException('\\InvalidArgumentException');
        $string = new Title('');
    }

    /**
     * @test
     */
    public function it_can_be_cast_to_a_string()
    {
        $string = new Title('String');
        $value = (string)$string;

        $this->assertEquals('String', $string);
    }

    /**
     * @test
     */
    public function it_can_be_serialized_to_a_json_string()
    {
        $string = new Title('String');
        $serializedString = json_encode($string);
        $this->assertEquals('"String"', $serializedString);
    }
}
