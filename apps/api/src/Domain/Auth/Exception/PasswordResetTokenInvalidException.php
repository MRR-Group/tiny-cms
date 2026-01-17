<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use Exception;

class PasswordResetTokenInvalidException extends Exception
{
    public function __construct()
    {
        parent::__construct("Password reset token is invalid");
    }
}
