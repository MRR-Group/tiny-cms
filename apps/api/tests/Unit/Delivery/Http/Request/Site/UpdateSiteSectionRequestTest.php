<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Site;

use App\Delivery\Http\Request\Site\UpdateSiteSectionRequest;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

class UpdateSiteSectionRequestTest extends TestCase
{
    public function testFromPsr7BuildsCommand(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/{$siteId}/sections/sec-1")
            ->withAttribute("id", $siteId)
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody([
                "title" => "Updated",
                "data" => ["images" => ["a.jpg"]],
            ]);

        $command = UpdateSiteSectionRequest::fromPsr7($request);

        $this->assertSame($siteId, $command->siteId);
        $this->assertSame("sec-1", $command->sectionId);
        $this->assertSame("Updated", $command->title);
        $this->assertSame("a.jpg", $command->data["images"][0]);
    }

    public function testFromPsr7ThrowsWhenSiteIdMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/x/sections/sec-1")
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["title" => "Updated", "data" => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        UpdateSiteSectionRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenSectionIdMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/x/sections/sec-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withParsedBody(["title" => "Updated", "data" => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section ID is required");

        UpdateSiteSectionRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenBodyIsInvalid(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method("getAttribute")
            ->willReturnMap([
                ["id", null, SiteId::generate()->toString()],
                ["sectionId", null, "sec-1"],
            ]);
        $request->method("getParsedBody")->willReturn("invalid");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid body");

        UpdateSiteSectionRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenTitleMissing(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/x/sections/sec-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section title is required");

        UpdateSiteSectionRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsWhenDataIsNotArray(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/sites/x/sections/sec-1")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["title" => "Updated", "data" => "invalid"]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section data must be an object");

        UpdateSiteSectionRequest::fromPsr7($request);
    }
}
