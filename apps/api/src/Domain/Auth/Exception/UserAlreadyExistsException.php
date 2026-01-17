<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use Exception;

class UserAlreadyExistsException extends Exception
{
    public function __construct(string $email)
    {
        parent::__construct("User with email '{$email}' already exists");
    }
}
