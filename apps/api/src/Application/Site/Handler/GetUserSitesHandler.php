<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Query\GetUserSitesQuery;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Site\Entity\Site;

class GetUserSitesHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @return array<Site>
     */
    public function handle(GetUserSitesQuery $query): array
    {
        $user = $this->userRepository->findById(new UserId($query->userId));

        if ($user === null) {
            // Or throw exception, or return empty if user not found (though user should exist if authenticated)
            return [];
        }

        return $user->getSites()->toArray();
    }
}
