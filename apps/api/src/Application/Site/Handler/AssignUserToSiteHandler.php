<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\AssignUserToSiteCommand;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class AssignUserToSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(AssignUserToSiteCommand $command): void
    {
        $site = $this->siteRepository->findById(new SiteId($command->siteId));
        if ($site === null) {
            throw new \InvalidArgumentException('Site not found');
        }

        $user = $this->userRepository->findById(new UserId($command->userId));
        if ($user === null) {
            throw new \InvalidArgumentException('User not found');
        }

        $site->addUser($user);
        $this->siteRepository->save($site);
    }
}
