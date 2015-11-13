<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

class Keyword extends String implements \JsonSerializable
{
    /**
     * @var bool
     */
    protected $visible;

    /**
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param string $value
     * @param bool $visible
     */
    public function __construct($value, $visible = true)
    {
        if (false !== strpos($value, ';')) {
            throw new \InvalidArgumentException('Keyword should not contain semicolons');
        }

        parent::__construct($value);
        $this->visible = $visible;

    }
}
