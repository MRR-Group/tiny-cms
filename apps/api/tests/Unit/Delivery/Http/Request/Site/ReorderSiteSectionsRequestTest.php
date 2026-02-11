<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Site;

use App\Delivery\Http\Request\Site\ReorderSiteSectionsRequest;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

class ReorderSiteSectionsRequestTest extends TestCase
{
    public function testFromPsr7ReturnsCommand(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/{$siteId}/sections/order")
            ->withAttribute("id", $siteId)
            ->withParsedBody([
                "sectionIds" => ["sec-2", "sec-1"],
            ]);

        $command = ReorderSiteSectionsRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
        $this->assertSame(["sec-2", "sec-1"], $command->sectionIds);
    }

    public function testFromPsr7ThrowsOnInvalidPayload(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/{$siteId}/sections/order")
            ->withAttribute("id", $siteId)
            ->withParsedBody([
                "sectionIds" => ["sec-1", ""],
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Each section id must be a non-empty string");

        ReorderSiteSectionsRequest::fromPsr7($request);
    }

    public function testFromPsr7UsesSiteIdFromBodyFallback(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/sections/order")
            ->withParsedBody([
                "siteId" => $siteId,
                "sectionIds" => ["sec-1", "sec-2"],
            ]);

        $command = ReorderSiteSectionsRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
    }

    public function testFromPsr7UsesBodySiteIdWhenAttributeIsNotString(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/sections/order")
            ->withAttribute("id", ["invalid"])
            ->withParsedBody([
                "siteId" => $siteId,
                "sectionIds" => ["sec-1", "sec-2"],
            ]);

        $command = ReorderSiteSectionsRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
    }

    public function testFromPsr7UsesBodySiteIdWhenAttributeIsEmptyString(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/sections/order")
            ->withAttribute("id", "   ")
            ->withParsedBody([
                "siteId" => $siteId,
                "sectionIds" => ["sec-1", "sec-2"],
            ]);

        $command = ReorderSiteSectionsRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
    }

    public function testFromPsr7TrimsBodySiteIdFallback(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/sections/order")
            ->withParsedBody([
                "siteId" => "  {$siteId}  ",
                "sectionIds" => ["sec-1", "sec-2"],
            ]);

        $command = ReorderSiteSectionsRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
    }

    public function testFromPsr7ThrowsWhenBodyIsInvalid(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method("getParsedBody")->willReturn("invalid");
        $request->method("getAttribute")->with("id")->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid body");

        ReorderSiteSectionsRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSiteIdIsMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/order")
            ->withParsedBody(["sectionIds" => ["sec-1"]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        ReorderSiteSectionsRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSectionIdsIsNotArray(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/x/sections/order")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withParsedBody(["sectionIds" => "invalid"]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("sectionIds must be an array");

        ReorderSiteSectionsRequest::fromPsr7($request);
    }
}
