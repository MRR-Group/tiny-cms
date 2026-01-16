<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\Contract\TokenIssuerInterface;
use App\Application\Auth\Contract\TokenValidatorInterface;
use App\Domain\Auth\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtTokenService implements TokenIssuerInterface, TokenValidatorInterface
{
    private string $key;

    public function __construct()
    {
        // In real app, inject via DI from config
        $this->key = $_ENV['JWT_SECRET'] ?? 'default_secret_change_me';
    }

    public function issue(User $user): string
    {
        $payload = [
            'iss' => 'tiny-cms',
            'sub' => $user->getId()->toString(),
            'role' => $user->getRole()->toString(),
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function validate(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return (array) $decoded;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
