<?php

namespace App\Exceptions;

use RuntimeException;

class SsoServerUnreachableException extends RuntimeException
{
    public function __construct(string $curlError, ?\Throwable $previous = null)
    {
        parent::__construct("Could not reach SSO server: {$curlError}", 0, $previous);
    }
}
