<?php
/**
 * @file
 */

namespace CultuurNet\Entry;

use RuntimeException;

class PrivateKeywordException extends \RuntimeException
{
    public function __construct(Rsp $rsp)
    {
        parent::__construct($rsp->getMessage());
    }
}
