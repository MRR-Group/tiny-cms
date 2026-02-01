<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Handler\GetSiteHandler;
use App\Application\Site\Query\GetSiteQuery;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;

class GetSiteHandlerTest extends TestCase
{
    public function testHandleReturnsSite(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $siteId = SiteId::generate();
        $site = $this->createMock(Site::class);

        $siteRepository->method("findById")->with($this->callback(fn(SiteId $id) => $id->equals($siteId)))->willReturn($site);

        $handler = new GetSiteHandler($siteRepository);
        $query = new GetSiteQuery((string)$siteId);

        $result = $handler->handle($query);

        $this->assertSame($site, $result);
    }

    public function testHandleThrowsIfSiteNotFound(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $siteRepository->method("findById")->willReturn(null);

        $handler = new GetSiteHandler($siteRepository);
        $query = new GetSiteQuery((string)SiteId::generate());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle($query);
    }
}
