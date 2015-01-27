<?php
/**
 * @file
 */

namespace CultuurNet\Entry;


abstract class RspException extends \Exception
{
    /**
     * @var Rsp
     */
    private $rsp;

    public function __construct(Rsp $rsp, $message = null)
    {
        $this->rsp = $rsp;
        parent::__construct($message);
    }

    public function getRsp() {
        return $this->rsp;
    }
}
