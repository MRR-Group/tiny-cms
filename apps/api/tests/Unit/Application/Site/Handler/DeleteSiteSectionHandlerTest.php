<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\DeleteSiteSectionCommand;
use App\Application\Site\Handler\DeleteSiteSectionHandler;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;

class DeleteSiteSectionHandlerTest extends TestCase
{
    public function testHandleRemovesSectionAndPersistsSite(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);

        $repository->method("findById")->willReturn($site);
        $site->expects($this->once())->method("removeSection")->with("sec-1");
        $repository->expects($this->once())->method("save")->with($site);

        $handler = new DeleteSiteSectionHandler($repository);
        $handler->handle(new DeleteSiteSectionCommand(SiteId::generate()->toString(), "sec-1"));

        $this->assertTrue(true);
    }

    public function testHandleThrowsWhenSiteMissing(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $repository->expects($this->once())->method("findById")->willReturn(null);

        $handler = new DeleteSiteSectionHandler($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new DeleteSiteSectionCommand(SiteId::generate()->toString(), "sec-1"));
    }
}
