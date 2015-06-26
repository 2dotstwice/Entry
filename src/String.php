<?php
/**
 * @file
 * String value object.
 */

namespace CultuurNet\Entry;

class String implements \JsonSerializable
{
    protected $value;

    public function __construct($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Value should be a string');
        }

        $value = trim($value);
        if ('' === $value) {
            throw new \InvalidArgumentException('Value should consist of at least one character');
        }

        $this->value = $value;
    }

    public function __toString()
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
