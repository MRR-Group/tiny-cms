<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Site;

use App\Delivery\Http\Request\Site\CreateSiteSectionRequest;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

class CreateSiteSectionRequestTest extends TestCase
{
    public function testFromPsr7ReturnsCommand(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/{$siteId}/sections")
            ->withAttribute("id", $siteId)
            ->withParsedBody([
                "type" => "text",
                "title" => "Hero",
            ]);

        $command = CreateSiteSectionRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
        $this->assertSame("text", $command->type);
        $this->assertSame("Hero", $command->title);
    }

    public function testFromPsr7ThrowsOnMissingTypeAndTitle(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withParsedBody([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section type and title are required");

        CreateSiteSectionRequest::fromPsr7($request);
    }

    public function testFromPsr7UsesSiteIdFromBodyFallback(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/sites/sections")
            ->withParsedBody([
                "siteId" => $siteId,
                "type" => "text",
                "title" => "Hero",
            ]);

        $command = CreateSiteSectionRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
    }

    public function testFromPsr7UsesBodySiteIdWhenAttributeIsNotString(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/sites/sections")
            ->withAttribute("id", ["invalid"])
            ->withParsedBody([
                "siteId" => $siteId,
                "type" => "text",
                "title" => "Hero",
            ]);

        $command = CreateSiteSectionRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
    }

    public function testFromPsr7UsesBodySiteIdWhenAttributeIsEmptyString(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/sites/sections")
            ->withAttribute("id", "   ")
            ->withParsedBody([
                "siteId" => $siteId,
                "type" => "text",
                "title" => "Hero",
            ]);

        $command = CreateSiteSectionRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
    }

    public function testFromPsr7TrimsBodySiteIdFallback(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/sites/sections")
            ->withParsedBody([
                "siteId" => "  {$siteId}  ",
                "type" => "text",
                "title" => "Hero",
            ]);

        $command = CreateSiteSectionRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
    }

    public function testFromPsr7ThrowsWhenBodyIsInvalid(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method("getParsedBody")->willReturn("invalid");
        $request->method("getAttribute")->with("id")->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid body");

        CreateSiteSectionRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSiteIdIsMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/x/sections")
            ->withParsedBody([
                "type" => "text",
                "title" => "Hero",
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        CreateSiteSectionRequest::fromPsr7($request);
    }
}
