<?php

namespace App\Exceptions;

use RuntimeException;

class SsoInvalidPublicKeyException extends RuntimeException
{
    public function __construct(string $reason, ?\Throwable $previous = null)
    {
        parent::__construct($reason, 0, $previous);
    }
}
