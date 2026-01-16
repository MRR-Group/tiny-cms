<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\ChangePasswordCommand;
use App\Domain\Auth\ValueObject\UserId;
use Psr\Http\Message\ServerRequestInterface;

class ChangePasswordRequest
{
    public static function fromPsr7(ServerRequestInterface $request): ChangePasswordCommand
    {
        $userIdStr = $request->getAttribute("user_id");
        $data = (array)$request->getParsedBody();

        $oldPassword = $data["old_password"] ?? "";
        $newPassword = $data["new_password"] ?? "";

        if (!$userIdStr || empty($oldPassword) || empty($newPassword)) {
            throw new \InvalidArgumentException("Missing required fields");
        }

        return new ChangePasswordCommand(
            new UserId($userIdStr),
            (string)$oldPassword,
            (string)$newPassword,
        );
    }
}
