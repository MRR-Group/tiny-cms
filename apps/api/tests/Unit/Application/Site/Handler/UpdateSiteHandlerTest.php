<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteCommand;
use App\Application\Site\Handler\UpdateSiteHandler;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Application\Site\Handler\UpdateSiteHandler
 */
class UpdateSiteHandlerTest extends TestCase
{
    private SiteRepositoryInterface&MockObject $repository;
    private UpdateSiteHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SiteRepositoryInterface::class);
        $this->handler = new UpdateSiteHandler($this->repository);
    }

    public function testHandleUpdatesSite(): void
    {
        $id = SiteId::generate();
        $command = new UpdateSiteCommand(
            $id->toString(),
            "New Name",
            "http://new-url.com",
            SiteType::DYNAMIC
        );

        $site = $this->createMock(Site::class);
        $site->expects($this->once())->method("updateName")->with("New Name");
        $site->expects($this->once())->method("updateUrl")->with("http://new-url.com");
        $site->expects($this->once())->method("updateType")->with(SiteType::DYNAMIC);

        $this->repository->expects($this->once())
            ->method("findById")
            ->with($this->callback(fn(SiteId $sid) => $sid->equals($id)))
            ->willReturn($site);

        $this->repository->expects($this->once())->method("save")->with($site);

        $this->handler->handle($command);
    }

    public function testHandleThrowsExceptionIfSiteNotFound(): void
    {
        $id = SiteId::generate();
        $command = new UpdateSiteCommand(
            $id->toString(),
            "New Name",
            "http://new-url.com",
            SiteType::DYNAMIC
        );

        $this->repository->expects($this->once())
            ->method("findById")
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $this->handler->handle($command);
    }
}
