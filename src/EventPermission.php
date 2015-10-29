<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

class EventPermission
{
    /**
     * @var string
     */
    private $cdbid;

    /**
     * @var bool
     */
    private $editable;

    /**
     * @param string $cdbid
     * @param bool $editable
     */
    public function __construct($cdbid, $editable)
    {
        if (!is_string($cdbid)) {
            throw new \InvalidArgumentException('Expected value for argument $cdbid to be a string, got ' . gettype($cdbid));
        }
        if (!is_bool($editable)) {
            throw new \InvalidArgumentException('Expected value for argument $editable to be a string, got ' . gettype($editable));
        }

        $this->cdbid = $cdbid;
        $this->editable = $editable;
    }

    /**
     * @return string
     */
    public function getCdbid()
    {
        return $this->cdbid;
    }

    /**
     * @return boolean
     */
    public function isEditable()
    {
        return $this->editable;
    }
}
