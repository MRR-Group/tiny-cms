<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Site;

use App\Application\Site\Command\UpdateSiteCommand;
use App\Delivery\Http\Request\Site\UpdateSiteRequest;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

#[CoversClass(UpdateSiteRequest::class)]
class UpdateSiteRequestTest extends TestCase
{
    public function testFromPsr7CreatesCommand(): void
    {
        $data = [
            "name" => "Site Name",
            "url" => "http://example.com",
            "type" => "dynamic",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $command = UpdateSiteRequest::fromPsr7($request);

        $this->assertInstanceOf(UpdateSiteCommand::class, $command);
        $this->assertEquals("123", $command->siteId);
        $this->assertEquals("Site Name", $command->name);
        $this->assertEquals("http://example.com", $command->url);
        $this->assertEquals(SiteType::DYNAMIC, $command->type);
    }

    public function testMissingSiteIdThrowsException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites");
        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        UpdateSiteRequest::fromPsr7($request);
    }
    
    public function testEmptySiteIdThrowsException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites")
             ->withAttribute("id", "");
        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testInvalidSiteIdTypeThrowsException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
             ->withAttribute("id", 123);
        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site ID is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testMissingNameThrowsException(): void
    {
        $data = [
            "url" => "http://example.com",
            "type" => "dynamic",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Name is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testInvalidNameThrowsException(): void
    {
        $data = [
            "name" => 123,
            "url" => "http://example.com",
            "type" => "dynamic",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Name is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testEmptyNameThrowsException(): void
    {
        $data = [
            "name" => "",
            "url" => "http://example.com",
            "type" => "dynamic",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Name is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testMissingUrlThrowsException(): void
    {
        $data = [
            "name" => "Name",
            "type" => "dynamic",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("URL is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testInvalidUrlThrowsException(): void
    {
        $data = [
            "name" => "Name",
            "url" => 123,
            "type" => "dynamic",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("URL is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testEmptyUrlThrowsException(): void
    {
        $data = [
            "name" => "Name",
            "url" => "",
            "type" => "dynamic",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("URL is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testMissingTypeThrowsException(): void
    {
        $data = [
            "name" => "Name",
            "url" => "url",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Type is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testInvalidTypeThrowsException(): void
    {
        $data = [
            "name" => "Name",
            "url" => "url",
            "type" => 123
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Type is required");

        UpdateSiteRequest::fromPsr7($request);
    }

    public function testInvalidEnumValueThrowsException(): void
    {
        $data = [
            "name" => "Name",
            "url" => "url",
            "type" => "invalid_enum"
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/sites/123")
            ->withAttribute("id", "123");
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid site type");

        UpdateSiteRequest::fromPsr7($request);
    }
}
