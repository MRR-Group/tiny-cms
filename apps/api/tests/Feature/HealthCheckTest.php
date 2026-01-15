<?php

declare(strict_types=1);

namespace TinyCms\Api\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use TinyCms\Api\Application;

final class HealthCheckTest extends TestCase
{
    public function testHealthEndpointReturnsOk(): void
    {
        $app = Application::create();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/health');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"status":"ok"}', (string) $response->getBody());
    }
}
