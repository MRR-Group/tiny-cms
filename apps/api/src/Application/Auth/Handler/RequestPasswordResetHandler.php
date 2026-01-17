<?php

declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\RequestPasswordResetCommand;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\Email;

class RequestPasswordResetHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function handle(RequestPasswordResetCommand $command): void
    {
        $user = $this->userRepository->findByEmail(new Email($command->email));

        if ($user === null) {
            // We don't want to leak if user exists or not
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = new \DateTimeImmutable("+1 hour");

        $user->setResetToken($token, $expiresAt);
        $this->userRepository->save($user);

        // TODO: Send email
        // For now we just log it or it can be retrieved from DB
        error_log("Password reset token for {$command->email}: {$token}");
    }
}
