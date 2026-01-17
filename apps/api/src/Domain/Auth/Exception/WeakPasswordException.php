<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use Exception;

class WeakPasswordException extends Exception
{
    public function __construct()
    {
        parent::__construct('Password does not meet security requirements');
    }
}
