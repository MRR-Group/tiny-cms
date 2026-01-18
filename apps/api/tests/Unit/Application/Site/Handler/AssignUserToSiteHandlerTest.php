<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\AssignUserToSiteCommand;
use App\Application\Site\Handler\AssignUserToSiteHandler;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Auth\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class AssignUserToSiteHandlerTest extends TestCase
{
    public function testHandleAssignsUserToSite(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $siteId = SiteId::generate();
        $userId = UserId::generate();

        $site = $this->createMock(Site::class);
        $user = $this->createMock(User::class);

        $siteRepository->method('findById')->with($this->callback(fn(SiteId $id) => $id->equals($siteId)))->willReturn($site);
        $userRepository->method('findById')->with($this->callback(fn(UserId $id) => $id->equals($userId)))->willReturn($user);

        $site->expects($this->once())->method('addUser')->with($user);
        $siteRepository->expects($this->once())->method('save')->with($site);

        $handler = new AssignUserToSiteHandler($siteRepository, $userRepository);
        $command = new AssignUserToSiteCommand((string) $userId, (string) $siteId);

        $handler->handle($command);
    }
}
