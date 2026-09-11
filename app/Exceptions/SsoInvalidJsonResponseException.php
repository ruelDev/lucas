<?php

namespace App\Exceptions;

use RuntimeException;

class SsoInvalidJsonResponseException extends RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('SSO server returned an invalid JSON response.', 0, $previous);
    }
}
