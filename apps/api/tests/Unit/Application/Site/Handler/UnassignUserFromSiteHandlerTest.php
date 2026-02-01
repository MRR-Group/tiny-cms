<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\UnassignUserFromSiteCommand;
use App\Application\Site\Handler\UnassignUserFromSiteHandler;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;

class UnassignUserFromSiteHandlerTest extends TestCase
{
    public function testHandleUnassignsUserFromSite(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $siteId = SiteId::generate();
        $userId = UserId::generate();

        $site = $this->createMock(Site::class);
        $user = $this->createMock(User::class);

        $siteRepository->method("findById")->with($this->callback(fn(SiteId $id) => $id->equals($siteId)))->willReturn($site);
        $userRepository->method("findById")->with($this->callback(fn(UserId $id) => $id->equals($userId)))->willReturn($user);

        $site->expects($this->once())->method("removeUser")->with($user);
        $siteRepository->expects($this->once())->method("save")->with($site);

        $handler = new UnassignUserFromSiteHandler($siteRepository, $userRepository);
        $command = new UnassignUserFromSiteCommand((string)$userId, (string)$siteId);

        $handler->handle($command);
    }

    public function testHandleThrowsIfSiteNotFound(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $siteRepository->method("findById")->willReturn(null);

        $handler = new UnassignUserFromSiteHandler($siteRepository, $userRepository);
        $command = new UnassignUserFromSiteCommand((string)UserId::generate(), (string)SiteId::generate());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle($command);
    }

    public function testHandleThrowsIfUserNotFound(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $site = $this->createMock(Site::class);
        $siteRepository->method("findById")->willReturn($site);
        $userRepository->method("findById")->willReturn(null);

        $handler = new UnassignUserFromSiteHandler($siteRepository, $userRepository);
        $command = new UnassignUserFromSiteCommand((string)UserId::generate(), (string)SiteId::generate());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("User not found");

        $handler->handle($command);
    }
}
