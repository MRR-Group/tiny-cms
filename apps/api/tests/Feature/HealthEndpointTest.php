<?php

declare(strict_types=1);

namespace TinyCMS\Api\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use TinyCMS\Api\Bootstrap;

final class HealthEndpointTest extends TestCase
{
    public function testHealthEndpointReturnsOk(): void
    {
        $app = Bootstrap::createApp();
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/health');

        $response = $app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame('{"status":"ok"}', (string) $response->getBody());
    }
}
