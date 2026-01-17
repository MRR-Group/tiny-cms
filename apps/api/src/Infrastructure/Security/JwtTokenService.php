<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\Contract\TokenIssuerInterface;
use App\Application\Auth\Contract\TokenValidatorInterface;
use App\Domain\Auth\Entity\User;
use App\Domain\Shared\Clock\ClockInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtTokenService implements TokenIssuerInterface, TokenValidatorInterface
{
    public function __construct(
        private ClockInterface $clock,
        private string $key,
    ) {}

    public function issue(User $user): string
    {
        $now = $this->clock->now()->getTimestamp();
        $payload = [
            "iss" => "tiny-cms",
            "sub" => $user->getId()->toString(),
            "role" => $user->getRole()->toString(),
            "iat" => $now,
            "exp" => $now + 3600, // 1 hour
        ];

        return JWT::encode($payload, $this->key, "HS256");
    }

    public function validate(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, "HS256"));

            return (array)$decoded;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
