<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\AddSiteSectionCommand;
use App\Application\Site\Handler\AddSiteSectionHandler;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;

class AddSiteSectionHandlerTest extends TestCase
{
    public function testHandleAddsSectionAndPersistsSite(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);

        $siteRepository->method("findById")->willReturn($site);
        $site->expects($this->once())
            ->method("addSection")
            ->with("text", "Hero")
            ->willReturn(["id" => "sec-1", "type" => "text", "title" => "Hero"]);

        $siteRepository->expects($this->once())->method("save")->with($site);

        $handler = new AddSiteSectionHandler($siteRepository);
        $result = $handler->handle(new AddSiteSectionCommand(
            SiteId::generate()->toString(),
            "text",
            "Hero",
        ));

        $this->assertSame("sec-1", $result["id"]);
        $this->assertSame("text", $result["type"]);
        $this->assertSame("Hero", $result["title"]);
    }

    public function testHandleThrowsWhenSiteMissing(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $siteRepository->method("findById")->willReturn(null);

        $handler = new AddSiteSectionHandler($siteRepository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new AddSiteSectionCommand(
            SiteId::generate()->toString(),
            "text",
            "Hero",
        ));
    }
}
