<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\DeleteSiteCommand;
use App\Application\Site\Handler\DeleteSiteHandler;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Application\Site\Handler\DeleteSiteHandler
 */
class DeleteSiteHandlerTest extends TestCase
{
    private SiteRepositoryInterface&MockObject $repository;
    private DeleteSiteHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SiteRepositoryInterface::class);
        $this->handler = new DeleteSiteHandler($this->repository);
    }

    public function testHandleDeletesSite(): void
    {
        $id = SiteId::generate();
        $command = new DeleteSiteCommand($id->toString());

        $site = $this->createMock(Site::class);
        $site->method("getEditorCount")->willReturn(0);

        $this->repository->expects($this->once())
            ->method("findById")
            ->with($this->callback(fn(SiteId $sid) => $sid->equals($id)))
            ->willReturn($site);

        $this->repository->expects($this->once())->method("delete")->with($site);

        $this->handler->handle($command);
    }

    public function testHandleThrowsExceptionIfSiteNotFound(): void
    {
        $id = SiteId::generate();
        $command = new DeleteSiteCommand($id->toString());

        $this->repository->expects($this->once())
            ->method("findById")
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $this->handler->handle($command);
    }

    public function testHandleThrowsExceptionIfSiteHasEditors(): void
    {
        $id = SiteId::generate();
        $command = new DeleteSiteCommand($id->toString());

        $site = $this->createMock(Site::class);
        $site->method("getEditorCount")->willReturn(1);

        $this->repository->expects($this->once())
            ->method("findById")
            ->willReturn($site);

        $this->repository->expects($this->never())->method("delete");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot delete site with assigned editors");

        $this->handler->handle($command);
    }
}
