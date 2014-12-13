<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use Exception;

class UnexpectedTranslationErrorException extends \Exception
{
    protected $rsp;

    public function __construct(Rsp $rsp)
    {
        $this->rsp = $rsp;
        parent::__construct('Pushing translation to UDB2 collaboration services failed with code: ' . $rsp->getCode());
    }
}
