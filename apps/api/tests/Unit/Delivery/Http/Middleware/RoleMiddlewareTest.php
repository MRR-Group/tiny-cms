<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Middleware;

use App\Delivery\Http\Middleware\RoleMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RoleMiddlewareTest extends TestCase
{
    public function testReturns403IfRoleMismatch(): void
    {
        $middleware = new RoleMiddleware("admin");
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $request->expects($this->once())
            ->method("getAttribute")
            ->with("role")
            ->willReturn("editor");

        $handler->expects($this->never())->method("handle");

        $response = $middleware->process($request, $handler);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testPassesIfRoleMatches(): void
    {
        $middleware = new RoleMiddleware("admin");
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = new Response();

        $request->expects($this->once())
            ->method("getAttribute")
            ->with("role")
            ->willReturn("admin");

        $handler->expects($this->once())
            ->method("handle")
            ->with($request)
            ->willReturn($response);

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }
}
