<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Middleware;

use App\Delivery\Http\Middleware\RoleMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class RoleMiddlewareTest extends TestCase
{
    public function testReturns403IfRoleDoesNotMatch(): void
    {
        $middleware = new RoleMiddleware("admin");
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withAttribute("role", "editor");

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString("Forbidden", (string)$response->getBody());
    }

    public function testReturns403IfRoleMissing(): void
    {
        $middleware = new RoleMiddleware("admin");
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/");

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testPassesIfRoleMatches(): void
    {
        $middleware = new RoleMiddleware("admin");
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withAttribute("role", "admin");

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method("handle")
            ->willReturn((new ResponseFactory())->createResponse());

        $middleware->process($request, $handler);
    }
}
