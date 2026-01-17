<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\RequestPasswordResetCommand;
use Psr\Http\Message\ServerRequestInterface;

class RequestPasswordResetRequest
{
    public static function fromPsr7(ServerRequestInterface $request): RequestPasswordResetCommand
    {
        $data = (array)$request->getParsedBody();
        $email = $data["email"] ?? "";

        if (empty($email)) {
            throw new \InvalidArgumentException("Email is required");
        }

        return new RequestPasswordResetCommand((string)$email);
    }
}
