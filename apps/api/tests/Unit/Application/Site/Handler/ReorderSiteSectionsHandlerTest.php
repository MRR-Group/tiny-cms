<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\ReorderSiteSectionsCommand;
use App\Application\Site\Handler\ReorderSiteSectionsHandler;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;

class ReorderSiteSectionsHandlerTest extends TestCase
{
    public function testHandleReordersSectionsAndPersistsSite(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);

        $siteRepository->method("findById")->willReturn($site);
        $site->expects($this->once())->method("reorderSections")->with(["sec-2", "sec-1"]);
        $siteRepository->expects($this->once())->method("save")->with($site);

        $handler = new ReorderSiteSectionsHandler($siteRepository);
        $handler->handle(new ReorderSiteSectionsCommand(SiteId::generate()->toString(), ["sec-2", "sec-1"]));
    }

    public function testHandleThrowsWhenSiteMissing(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $siteRepository->method("findById")->willReturn(null);

        $handler = new ReorderSiteSectionsHandler($siteRepository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new ReorderSiteSectionsCommand(SiteId::generate()->toString(), ["sec-1"]));
    }
}
