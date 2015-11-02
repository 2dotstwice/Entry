<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

class Keyword extends String implements \JsonSerializable
{
    public function __construct($value)
    {
        parent::__construct($value);

        if (false !== strpos($value, ';')) {
            throw new \InvalidArgumentException('Keyword should not contain semicolons');
        }
        $this->value = $value;
    }
}
