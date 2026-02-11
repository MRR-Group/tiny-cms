<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Site;

use App\Delivery\Http\Request\Site\UpdateSiteSectionItemRequest;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class UpdateSiteSectionItemRequestTest extends TestCase
{
    public function testFromPsr7ReturnsCommand(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/{$siteId}/sections/sec-1/items/item-1")
            ->withAttribute("id", $siteId)
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => ["title" => "Updated"]]);

        $command = UpdateSiteSectionItemRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
        $this->assertSame("sec-1", $command->sectionId);
        $this->assertSame("item-1", $command->itemId);
        $this->assertSame(["title" => "Updated"], $command->data);
    }

    public function testFromPsr7ThrowsWhenItemIdMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => ["title" => "Updated"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item ID is required");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSiteIdIsNotString(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", ["bad"])
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => ["title" => "Updated"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSectionIdEmptyString(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => ["title" => "Updated"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section ID is required");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => "bad"]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data is required");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataKeyMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data is required");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataIsEmptyObject(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data must be a non-empty object");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataContainsReservedCreatedAtField(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => ["createdAt" => "2026-01-01T00:00:00+00:00", "title" => "Updated"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data contains reserved field: createdAt");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataContainsReservedIdField(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => ["id" => "client-controlled", "title" => "Updated"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data contains reserved field: id");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataIsListArray(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/sec-1/items/item-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => ["title"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data must be a non-empty object");

        UpdateSiteSectionItemRequest::fromPsr7($request);
    }
}
