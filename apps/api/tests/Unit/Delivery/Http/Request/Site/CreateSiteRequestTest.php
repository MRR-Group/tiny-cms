<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Site;

use App\Delivery\Http\Request\Site\CreateSiteRequest;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class CreateSiteRequestTest extends TestCase
{
    public function testFromPsr7ReturnsCommand(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/sites")
            ->withParsedBody([
                "name" => "My Site",
                "url" => "http://example.com",
                "type" => SiteType::DYNAMIC->value,
            ]);

        $command = CreateSiteRequest::fromPsr7($request);

        $this->assertEquals("My Site", $command->name);
        $this->assertEquals("http://example.com", $command->url);
        $this->assertEquals(SiteType::DYNAMIC->value, $command->type);
    }

    public function testFromPsr7ThrowsOnInvalidBodyType(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/sites")
            ->withParsedBody(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid body");

        CreateSiteRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsOnMissingFields(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/sites")
            ->withParsedBody([
                "name" => "My Site",
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required fields: name, url, type");

        CreateSiteRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsOnInvalidSiteType(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/sites")
            ->withParsedBody([
                "name" => "My Site",
                "url" => "http://example.com",
                "type" => "invalid_type",
            ]);



        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid type");

        CreateSiteRequest::fromPsr7($request);
    }
}
