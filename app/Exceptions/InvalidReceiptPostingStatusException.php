<?php

namespace App\Exceptions;

use Exception;

class InvalidReceiptPostingStatusException extends Exception
{
    public function __construct(
        string $currentStatus,
        string $requiredStatus,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = sprintf(
            'Out collection must be in %s status to be sent back. Current status: %s',
            $requiredStatus,
            $currentStatus
        );

        parent::__construct($message, $code, $previous);
    }
}
