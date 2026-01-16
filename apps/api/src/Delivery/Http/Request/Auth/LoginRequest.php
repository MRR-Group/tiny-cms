<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\LoginCommand;
use Psr\Http\Message\ServerRequestInterface;

class LoginRequest
{
    public static function fromPsr7(ServerRequestInterface $request): LoginCommand
    {
        $data = $request->getParsedBody();

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new \InvalidArgumentException('Email and password are required');
        }

        return new LoginCommand((string) $email, (string) $password);
    }
}
