<?php

declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Query\ListUsersQuery;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepositoryInterface;

class ListUsersHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @return array<User>
     */
    public function handle(ListUsersQuery $query): array
    {
        return $this->userRepository->findAll();
    }
}
