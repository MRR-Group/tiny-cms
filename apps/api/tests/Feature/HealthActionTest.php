<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

final class HealthActionTest extends TestCase
{
    public function testHealthEndpointReturns200(): void
    {
        $app = Application::create();

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/health");
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testHealthEndpointReturnsJson(): void
    {
        $app = Application::create();

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/health");
        $response = $app->handle($request);

        $this->assertStringContainsString("application/json", $response->getHeaderLine("Content-Type"));
    }

    public function testHealthEndpointReturnsStatusOk(): void
    {
        $app = Application::create();

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/health");
        $response = $app->handle($request);

        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        $this->assertArrayHasKey("status", $data);
        $this->assertEquals("ok", $data["status"]);
    }

    public function testHealthEndpointReturnsTimestamp(): void
    {
        $app = Application::create();

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/health");
        $response = $app->handle($request);

        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        $this->assertArrayHasKey("timestamp", $data);
    }

    public function testHealthEndpointReturnsVersion(): void
    {
        $app = Application::create();

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/health");
        $response = $app->handle($request);

        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        $this->assertArrayHasKey("version", $data);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $data["version"]);
    }
}
