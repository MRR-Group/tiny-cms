<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\ConfirmPasswordResetCommand;
use Psr\Http\Message\ServerRequestInterface;

class ConfirmPasswordResetRequest
{
    public static function fromPsr7(ServerRequestInterface $request): ConfirmPasswordResetCommand
    {
        $data = (array)$request->getParsedBody();
        $token = $data["token"] ?? "";
        $password = $data["password"] ?? "";

        if (empty($token) || empty($password)) {
            throw new \InvalidArgumentException("Token and password are required");
        }

        return new ConfirmPasswordResetCommand((string)$token, (string)$password);
    }
}
