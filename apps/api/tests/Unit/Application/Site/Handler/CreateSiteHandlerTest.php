<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\CreateSiteCommand;
use App\Application\Site\Handler\CreateSiteHandler;
use App\Domain\Shared\Clock\ClockInterface;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\TestCase;

class CreateSiteHandlerTest extends TestCase
{
    public function testHandleCreatesSite(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $clock = $this->createMock(ClockInterface::class);
        $now = new \DateTimeImmutable();
        $clock->method('now')->willReturn($now);

        $handler = new CreateSiteHandler($siteRepository, $clock);
        $command = new CreateSiteCommand('My Site', 'https://example.com', 'static');

        $siteRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Site $site) use ($now) {
                return $site->getName() === 'My Site'
                    && $site->getUrl() === 'https://example.com'
                    && $site->getType() === SiteType::STATIC
                    && $site->getCreatedAt() === $now;
            }));

        $siteId = $handler->handle($command);

        $this->assertInstanceOf(SiteId::class, $siteId);
    }
}
