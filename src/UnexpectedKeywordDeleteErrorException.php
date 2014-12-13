<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use Exception;

class UnexpectedKeywordDeleteErrorException extends \Exception
{
    protected $rsp;

    public function __construct(Rsp $rsp)
    {
        $this->rsp = $rsp;
        parent::__construct('Deleting keyword failed with code: ' . $rsp->getCode());
    }
}
