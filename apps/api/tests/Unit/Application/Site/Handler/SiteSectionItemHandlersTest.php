<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\AddSiteSectionItemCommand;
use App\Application\Site\Command\DeleteSiteSectionItemCommand;
use App\Application\Site\Command\UpdateSiteSectionItemCommand;
use App\Application\Site\Handler\AddSiteSectionItemHandler;
use App\Application\Site\Handler\DeleteSiteSectionItemHandler;
use App\Application\Site\Handler\ListSiteSectionItemsHandler;
use App\Application\Site\Handler\UpdateSiteSectionItemHandler;
use App\Application\Site\Query\ListSiteSectionItemsQuery;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;

class SiteSectionItemHandlersTest extends TestCase
{
    public function testAddSiteSectionItemHandlerAddsItemAndSavesSite(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);

        $repository->expects($this->once())->method("findById")->willReturn($site);
        $site->expects($this->once())
            ->method("addSectionItem")
            ->with("sec-1", ["title" => "Card"]) 
            ->willReturn(["id" => "item-1", "title" => "Card"]);
        $repository->expects($this->once())->method("save")->with($site);

        $handler = new AddSiteSectionItemHandler($repository);
        $result = $handler->handle(new AddSiteSectionItemCommand(SiteId::generate()->toString(), "sec-1", ["title" => "Card"]));

        $this->assertSame("item-1", $result["id"]);
        $this->assertSame("Card", $result["title"]);
    }

    public function testAddSiteSectionItemHandlerThrowsWhenSiteNotFound(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $repository->expects($this->once())->method("findById")->willReturn(null);

        $handler = new AddSiteSectionItemHandler($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new AddSiteSectionItemCommand(SiteId::generate()->toString(), "sec-1", ["title" => "Card"]));
    }

    public function testUpdateSiteSectionItemHandlerUpdatesItemAndSavesSite(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);

        $repository->expects($this->once())->method("findById")->willReturn($site);
        $site->expects($this->once())
            ->method("updateSectionItem")
            ->with("sec-1", "item-1", ["title" => "Updated"]) 
            ->willReturn(["id" => "item-1", "title" => "Updated"]);
        $repository->expects($this->once())->method("save")->with($site);

        $handler = new UpdateSiteSectionItemHandler($repository);
        $result = $handler->handle(new UpdateSiteSectionItemCommand(SiteId::generate()->toString(), "sec-1", "item-1", ["title" => "Updated"]));

        $this->assertSame("Updated", $result["title"]);
    }

    public function testUpdateSiteSectionItemHandlerThrowsWhenSiteNotFound(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $repository->expects($this->once())->method("findById")->willReturn(null);

        $handler = new UpdateSiteSectionItemHandler($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new UpdateSiteSectionItemCommand(SiteId::generate()->toString(), "sec-1", "item-1", ["title" => "Updated"]));
    }

    public function testDeleteSiteSectionItemHandlerRemovesItemAndSavesSite(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);

        $repository->expects($this->once())->method("findById")->willReturn($site);
        $site->expects($this->once())->method("removeSectionItem")->with("sec-1", "item-1");
        $repository->expects($this->once())->method("save")->with($site);

        $handler = new DeleteSiteSectionItemHandler($repository);
        $handler->handle(new DeleteSiteSectionItemCommand(SiteId::generate()->toString(), "sec-1", "item-1"));
    }

    public function testDeleteSiteSectionItemHandlerThrowsWhenSiteNotFound(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $repository->expects($this->once())->method("findById")->willReturn(null);

        $handler = new DeleteSiteSectionItemHandler($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new DeleteSiteSectionItemCommand(SiteId::generate()->toString(), "sec-1", "item-1"));
    }

    public function testListSiteSectionItemsHandlerReturnsItems(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $site = $this->createMock(Site::class);

        $repository->expects($this->once())->method("findById")->willReturn($site);
        $site->expects($this->once())
            ->method("listSectionItems")
            ->with("sec-1")
            ->willReturn([["id" => "item-1"]]);

        $handler = new ListSiteSectionItemsHandler($repository);
        $result = $handler->handle(new ListSiteSectionItemsQuery(SiteId::generate()->toString(), "sec-1"));

        $this->assertSame("item-1", $result[0]["id"]);
    }

    public function testListSiteSectionItemsHandlerThrowsWhenSiteNotFound(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $repository->expects($this->once())->method("findById")->willReturn(null);

        $handler = new ListSiteSectionItemsHandler($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $handler->handle(new ListSiteSectionItemsQuery(SiteId::generate()->toString(), "sec-1"));
    }
}
