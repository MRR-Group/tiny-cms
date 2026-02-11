<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteSectionCommand;
use App\Application\Site\Handler\UpdateSiteSectionHandler;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;

class UpdateSiteSectionHandlerTest extends TestCase
{
    public function testHandleUpdatesSectionAndPersistsSite(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);

        $repository->method("findById")->willReturn($site);
        $site->expects($this->once())
            ->method("updateSection")
            ->with("sec-1", "Updated", ["images" => ["a.jpg"]])
            ->willReturn(["id" => "sec-1", "title" => "Updated"]);

        $repository->expects($this->once())->method("save")->with($site);

        $handler = new UpdateSiteSectionHandler($repository);
        $result = $handler->handle(new UpdateSiteSectionCommand(
            SiteId::generate()->toString(),
            "sec-1",
            "Updated",
            ["images" => ["a.jpg"]],
        ));

        $this->assertSame("Updated", $result["title"]);
    }

    public function testHandleThrowsWhenSiteMissing(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $repository->expects($this->once())->method("findById")->willReturn(null);

        $handler = new UpdateSiteSectionHandler($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new UpdateSiteSectionCommand(
            SiteId::generate()->toString(),
            "sec-1",
            "Updated",
            ["images" => ["a.jpg"]],
        ));
    }
}
