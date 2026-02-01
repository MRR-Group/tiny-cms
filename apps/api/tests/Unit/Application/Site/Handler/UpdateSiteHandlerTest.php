<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteCommand;
use App\Application\Site\Handler\UpdateSiteHandler;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateSiteHandler::class)]
class UpdateSiteHandlerTest extends TestCase
{
    private SiteRepositoryInterface&MockObject $repository;
    private UpdateSiteHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SiteRepositoryInterface::class);
        $this->handler = new UpdateSiteHandler($this->repository);
    }

    public function testHandleUpdatesSiteWithNormalization(): void
    {
        $id = SiteId::generate();
        // Input: "new-url.com" -> Output: "https://www.new-url.com/"
        $command = new UpdateSiteCommand(
            $id->toString(),
            "New Name",
            "new-url.com",
            SiteType::DYNAMIC,
        );

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        
        $site->expects($this->once())->method("updateUrl")->with("https://www.new-url.com/");

        $this->repository->method("findById")->willReturn($site);
        // Duplicate check should return null or the same site
        $this->repository->method("findByUrl")->willReturn(null);

        $this->handler->handle($command);
    }

    public function testHandleThrowsIfSiteNotFound(): void
    {
        $id = SiteId::generate();
        $command = new UpdateSiteCommand($id->toString(), "Name", "url.com", SiteType::STATIC);

        $this->repository->method("findById")->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $this->handler->handle($command);
    }

    public function testHandleThrowsIfUrlAlreadyTakenByAnotherSite(): void
    {
        $id = SiteId::generate();
        $otherId = SiteId::generate();
        $command = new UpdateSiteCommand($id->toString(), "Name", "taken.com", SiteType::STATIC);

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $this->repository->method("findById")->willReturn($site);

        $otherSite = $this->createMock(Site::class);
        $otherSite->method("getId")->willReturn($otherId);
        $this->repository->method("findByUrl")->with("https://www.taken.com/")->willReturn($otherSite);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site with URL 'https://www.taken.com/' already exists");

        $this->handler->handle($command);
    }
}
