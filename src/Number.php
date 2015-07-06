<?php
/**
 * @file
 * String value object.
 */

namespace CultuurNet\Entry;

class Number implements \JsonSerializable
{
    protected $value;

    public function __construct($value)
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('Value should be numeric');
        }

        $this->value = $value;
    }

    public function getNumber()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}
