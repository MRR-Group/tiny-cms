<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Handler\ListSitesHandler;
use App\Application\Site\Query\ListSitesQuery;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListSitesHandlerTest extends TestCase
{
    public function testHandleReturnsAllSites(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $handler = new ListSitesHandler($repository);

        $site = $this->createMock(Site::class);
        $repository->expects($this->once())->method('findAll')->willReturn([$site]);

        $result = $handler->handle(new ListSitesQuery());

        $this->assertCount(1, $result);
        $this->assertSame($site, $result[0]);
    }
}
