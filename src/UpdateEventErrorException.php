<?php

/**
 * @file
 * Contains CultuurNet\Entry\UpdateEventErrorException.
 */

namespace CultuurNet\Entry;

/**
 * Error exception when udb2 update failed.
 */
class UpdateEventErrorException extends RspException
{
    public function __construct(Rsp $rsp)
    {
        $message = 'UDB2 event updated failed, error code: ' . $rsp->getCode();
        parent::__construct(
            $rsp,
            $message
        );
    }
}
