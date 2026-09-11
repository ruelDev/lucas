<?php

namespace App\Exceptions;

use RuntimeException;

class SsoUnexpectedHttpStatusException extends RuntimeException
{
    public function __construct(int $httpCode, ?\Throwable $previous = null)
    {
        parent::__construct("SSO server returned HTTP {$httpCode}. Expected 200.", $httpCode, $previous);
    }
}
