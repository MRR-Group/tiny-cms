<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\CreateUserCommand;
use Psr\Http\Message\ServerRequestInterface;

class CreateUserRequest
{
    public static function fromPsr7(ServerRequestInterface $request): CreateUserCommand
    {
        $data = (array)$request->getParsedBody();

        $email = $data["email"] ?? "";
        $password = $data["password"] ?? "";
        $role = $data["role"] ?? "editor"; // Default role

        if (empty($email) || empty($password)) {
            throw new \InvalidArgumentException("Email and password are required");
        }

        return new CreateUserCommand((string)$email, (string)$password, (string)$role);
    }
}
