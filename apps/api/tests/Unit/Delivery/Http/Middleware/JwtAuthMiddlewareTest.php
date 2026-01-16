<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Middleware;

use App\Application\Auth\Contract\TokenValidatorInterface;
use App\Delivery\Http\Middleware\JwtAuthMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class JwtAuthMiddlewareTest extends TestCase
{
    private JwtAuthMiddleware $middleware;
    private TokenValidatorInterface&MockObject $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(TokenValidatorInterface::class);
        $this->middleware = new JwtAuthMiddleware($this->validator);
    }

    public function testReturns401IfHeaderMissing(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $request->expects($this->once())
            ->method("getHeaderLine")
            ->with("Authorization")
            ->willReturn("");

        $handler->expects($this->never())->method("handle");

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testReturns401IfTokenInvalid(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $request->method("getHeaderLine")->willReturn("Bearer invalid_token");

        $this->validator->expects($this->once())
            ->method("validate")
            ->with("invalid_token")
            ->willReturn(null);

        $handler->expects($this->never())->method("handle");

        $response = $this->middleware->process($request, $handler);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testPassesIfTokenValid(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = new Response();

        $request->method("getHeaderLine")->willReturn("Bearer valid_token");

        $claims = ["sub" => "user_id", "role" => "admin"];
        $this->validator->expects($this->once())
            ->method("validate")
            ->with("valid_token")
            ->willReturn($claims);

        $request->expects($this->exactly(2))
            ->method("withAttribute")
            ->willReturnSelf();

        $handler->expects($this->once())
            ->method("handle")
            ->with($request)
            ->willReturn($response);

        $result = $this->middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }
}
