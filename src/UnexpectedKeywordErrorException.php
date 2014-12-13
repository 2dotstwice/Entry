<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use RuntimeException;

class UnexpectedKeywordErrorException extends \RuntimeException
{
    public function __construct(Rsp $rsp)
    {
        parent::__construct($rsp->getMessage());
    }
}
