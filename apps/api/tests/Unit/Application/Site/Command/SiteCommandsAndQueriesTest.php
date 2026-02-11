<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Command;

use App\Application\Site\Command\AddSiteSectionCommand;
use App\Application\Site\Command\AddSiteSectionItemCommand;
use App\Application\Site\Command\AssignUserToSiteCommand;
use App\Application\Site\Command\CreateSiteCommand;
use App\Application\Site\Command\DeleteSiteCommand;
use App\Application\Site\Command\DeleteSiteSectionCommand;
use App\Application\Site\Command\DeleteSiteSectionItemCommand;
use App\Application\Site\Command\ReorderSiteSectionsCommand;
use App\Application\Site\Command\UnassignUserFromSiteCommand;
use App\Application\Site\Command\UpdateSiteCommand;
use App\Application\Site\Command\UpdateSiteSectionCommand;
use App\Application\Site\Command\UpdateSiteSectionItemCommand;
use App\Application\Site\Query\GetSiteQuery;
use App\Application\Site\Query\GetUserSitesQuery;
use App\Application\Site\Query\ListSiteSectionItemsQuery;
use App\Application\Site\Query\ListSiteSectionsQuery;
use App\Application\Site\Query\ListSitesQuery;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\TestCase;

class SiteCommandsAndQueriesTest extends TestCase
{
    public function testCreateAndUpdateSiteCommandsExposePayload(): void
    {
        $create = new CreateSiteCommand("Docs", "https://docs.example.com", "static");
        $update = new UpdateSiteCommand("site-1", "Docs V2", "https://docs-v2.example.com", SiteType::DYNAMIC);

        $this->assertSame("Docs", $create->name);
        $this->assertSame("https://docs.example.com", $create->url);
        $this->assertSame("static", $create->type);

        $this->assertSame("site-1", $update->siteId);
        $this->assertSame("Docs V2", $update->name);
        $this->assertSame("https://docs-v2.example.com", $update->url);
        $this->assertSame(SiteType::DYNAMIC, $update->type);
    }

    public function testUserAndSiteLinkCommandsExposePayload(): void
    {
        $assign = new AssignUserToSiteCommand("user-1", "site-1");
        $unassign = new UnassignUserFromSiteCommand("user-2", "site-2");
        $delete = new DeleteSiteCommand("site-3");

        $this->assertSame("user-1", $assign->userId);
        $this->assertSame("site-1", $assign->siteId);
        $this->assertSame("user-2", $unassign->userId);
        $this->assertSame("site-2", $unassign->siteId);
        $this->assertSame("site-3", $delete->siteId);
    }

    public function testSectionCommandsExposePayload(): void
    {
        $add = new AddSiteSectionCommand("site-1", "text", "Hero");
        $update = new UpdateSiteSectionCommand("site-1", "sec-1", "Hero Updated", ["enabled" => true]);
        $delete = new DeleteSiteSectionCommand("site-1", "sec-1");
        $reorder = new ReorderSiteSectionsCommand("site-1", ["sec-2", "sec-1"]);

        $this->assertSame("site-1", $add->siteId);
        $this->assertSame("text", $add->type);
        $this->assertSame("Hero", $add->title);

        $this->assertSame("site-1", $update->siteId);
        $this->assertSame("sec-1", $update->sectionId);
        $this->assertSame("Hero Updated", $update->title);
        $this->assertSame(["enabled" => true], $update->data);

        $this->assertSame("site-1", $delete->siteId);
        $this->assertSame("sec-1", $delete->sectionId);

        $this->assertSame("site-1", $reorder->siteId);
        $this->assertSame(["sec-2", "sec-1"], $reorder->sectionIds);
    }

    public function testSectionItemCommandsExposePayload(): void
    {
        $add = new AddSiteSectionItemCommand("site-1", "sec-1", ["title" => "Card"]);
        $update = new UpdateSiteSectionItemCommand("site-1", "sec-1", "item-1", ["title" => "Card Updated"]);
        $delete = new DeleteSiteSectionItemCommand("site-1", "sec-1", "item-1");

        $this->assertSame("site-1", $add->siteId);
        $this->assertSame("sec-1", $add->sectionId);
        $this->assertSame(["title" => "Card"], $add->data);

        $this->assertSame("site-1", $update->siteId);
        $this->assertSame("sec-1", $update->sectionId);
        $this->assertSame("item-1", $update->itemId);
        $this->assertSame(["title" => "Card Updated"], $update->data);

        $this->assertSame("site-1", $delete->siteId);
        $this->assertSame("sec-1", $delete->sectionId);
        $this->assertSame("item-1", $delete->itemId);
    }

    public function testQueriesExposePayload(): void
    {
        $getSite = new GetSiteQuery("site-1");
        $getUserSites = new GetUserSitesQuery("user-1");
        $listSections = new ListSiteSectionsQuery("site-1");
        $listItems = new ListSiteSectionItemsQuery("site-1", "sec-1");
        $listSites = new ListSitesQuery();

        $this->assertSame("site-1", $getSite->id);
        $this->assertSame("user-1", $getUserSites->userId);
        $this->assertSame("site-1", $listSections->siteId);
        $this->assertSame("site-1", $listItems->siteId);
        $this->assertSame("sec-1", $listItems->sectionId);
        $this->assertInstanceOf(ListSitesQuery::class, $listSites);
    }
}
