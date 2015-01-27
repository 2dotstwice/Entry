<?php
/**
 * @file
 */

namespace CultuurNet\Entry;


class CreateEventErrorException extends RspException
{
    public function __construct(Rsp $rsp)
    {
        $message = 'UDB2 event creation failed, error code: ' . $rsp->getCode();
        parent::__construct(
            $rsp,
            $message
        );
    }
}
