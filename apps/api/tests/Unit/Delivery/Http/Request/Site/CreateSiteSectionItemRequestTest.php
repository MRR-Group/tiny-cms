<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Site;

use App\Delivery\Http\Request\Site\CreateSiteSectionItemRequest;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class CreateSiteSectionItemRequestTest extends TestCase
{
    public function testFromPsr7ReturnsCommand(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/{$siteId}/sections/sec-1/items")
            ->withAttribute("id", $siteId)
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => ["title" => "Card"]]);

        $command = CreateSiteSectionItemRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
        $this->assertSame("sec-1", $command->sectionId);
        $this->assertSame(["title" => "Card"], $command->data);
    }

    public function testFromPsr7ThrowsWhenSiteIdMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections/sec-1/items")
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => ["title" => "Card"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        CreateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSiteIdIsNotString(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections/sec-1/items")
            ->withAttribute("id", ["bad"])
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => ["title" => "Card"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        CreateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSectionIdMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections/sec-1/items")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withParsedBody(["data" => ["title" => "Card"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section ID is required");

        CreateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSectionIdEmptyString(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections/sec-1/items")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "")
            ->withParsedBody(["data" => ["title" => "Card"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section ID is required");

        CreateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections/sec-1/items")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data is required");

        CreateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataIsEmptyObject(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections/sec-1/items")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data must be a non-empty object");

        CreateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataContainsReservedIdField(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections/sec-1/items")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => ["id" => "client-controlled", "title" => "Card"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data contains reserved field: id");

        CreateSiteSectionItemRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataIsListArray(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections/sec-1/items")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => ["title"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data must be a non-empty object");

        CreateSiteSectionItemRequest::fromPsr7($request);
    }
}
