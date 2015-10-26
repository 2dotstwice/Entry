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
    private $isEditable;

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
    public function isIsEditable()
    {
        return $this->isEditable;
    }

    /**
     * @param $cdbid
     * @param $isEditable
     */
    public function __construct($cdbid, $isEditable)
    {
        $this->cdbid = $cdbid;
        $this->isEditable= $isEditable;
    }
}
