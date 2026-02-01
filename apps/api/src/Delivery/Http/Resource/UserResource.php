<?php

declare(strict_types=1);

namespace App\Delivery\Http\Resource;

use App\Domain\Auth\Entity\User;

class UserResource
{
    /**
     * @return array{id: string, email: string, role: string}
     */
    public static function toArray(User $user): array
    {
        return [
            "id" => (string)$user->getId(),
            "email" => (string)$user->getEmail(),
            "role" => $user->getRole()->toString(),
        ];
    }

    /**
     * @param array<User> $users
     * @return array<int, array{id: string, email: string, role: string}>
     */
    public static function collectionToArray(array $users): array
    {
        return array_map(fn(User $user) => self::toArray($user), $users);
    }
}
