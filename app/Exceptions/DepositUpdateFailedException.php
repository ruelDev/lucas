<?php

namespace App\Exceptions;

use Exception;

class DepositUpdateFailedException extends Exception
{
    public function __construct(
        int $depositId,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = sprintf('Failed to update deposit ID: %s', $depositId);

        parent::__construct($message, $code, $previous);
    }
}
