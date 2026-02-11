<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Handler\ListSiteSectionsHandler;
use App\Application\Site\Query\ListSiteSectionsQuery;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;

class ListSiteSectionsHandlerTest extends TestCase
{
    public function testHandleReturnsSections(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);
        $site->method("getSections")->willReturn([
            ["id" => "sec-1", "type" => "text", "title" => "Intro", "position" => 0],
        ]);

        $siteRepository->method("findById")->willReturn($site);

        $handler = new ListSiteSectionsHandler($siteRepository);
        $sections = $handler->handle(new ListSiteSectionsQuery(SiteId::generate()->toString()));

        $this->assertCount(1, $sections);
        $this->assertSame("sec-1", $sections[0]["id"]);
    }

    public function testHandleThrowsWhenSiteMissing(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $siteRepository->method("findById")->willReturn(null);

        $handler = new ListSiteSectionsHandler($siteRepository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new ListSiteSectionsQuery(SiteId::generate()->toString()));
    }
}
