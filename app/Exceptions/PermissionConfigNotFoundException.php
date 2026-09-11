<?php

namespace App\Exceptions;

use Exception;

class PermissionConfigNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Error: config/permission.php not found and defaults could not be merged. ' .
                'Please publish the package configuration before proceeding, or drop the tables manually.'
        );
    }
}
