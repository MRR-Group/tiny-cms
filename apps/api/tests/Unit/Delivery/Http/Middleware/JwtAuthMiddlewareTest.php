<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Middleware;

use App\Application\Auth\Contract\TokenValidatorInterface;
use App\Delivery\Http\Middleware\JwtAuthMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class JwtAuthMiddlewareTest extends TestCase
{
    private TokenValidatorInterface&MockObject $validator;
    private JwtAuthMiddleware $middleware;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(TokenValidatorInterface::class);
        $this->middleware = new JwtAuthMiddleware($this->validator);
    }

    public function testReturns401IfNoAuthHeader(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/");
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString("Unauthorized", (string)$response->getBody());
    }

    public function testReturns401IfHeaderFormatInvalid(): void
    {
        // Missing "Bearer"
        $request1 = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withHeader("Authorization", "Basic user:pass");

        // Missing space
        $request2 = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withHeader("Authorization", "BearerToken");

        // Lowercase bearer (regex uses i flag, so this SHOULD pass)
        // Testing that regex handles case insensitivity correctly (mutant removing 'i' flag will fail this)
        $request3 = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withHeader("Authorization", "bearer token");

        $handler = $this->createMock(RequestHandlerInterface::class);

        // Case 1: Invalid format
        $response1 = $this->middleware->process($request1, $handler);
        $this->assertEquals(401, $response1->getStatusCode());

        // Case 2: Invalid format
        $response2 = $this->middleware->process($request2, $handler);
        $this->assertEquals(401, $response2->getStatusCode());

        // Case 3: Valid format (different case), should call validator
        $this->validator->expects($this->once())
            ->method("validate")
            ->with("token")
            ->willReturn(["sub" => "123", "role" => "admin"]);

        $handler->expects($this->once())->method("handle")->willReturn((new ResponseFactory())->createResponse());

        $this->middleware->process($request3, $handler);
    }

    public function testReturns401IfTokenInvalid(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withHeader("Authorization", "Bearer invalid_token");

        $this->validator->method("validate")->with("invalid_token")->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString("Invalid token", (string)$response->getBody());
    }

    public function testPassesIfTokenValid(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withHeader("Authorization", "Bearer valid_token");

        $this->validator->method("validate")->with("valid_token")->willReturn([
            "sub" => "user-123",
            "role" => "admin",
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method("handle")
            ->with($this->callback(fn(ServerRequestInterface $req) => $req->getAttribute("user_id") === "user-123"
                && $req->getAttribute("role") === "admin"))
            ->willReturn((new ResponseFactory())->createResponse());

        $this->middleware->process($request, $handler);
    }

    public function testRejectsTokenWithExtraData(): void
    {
        $token = "valid_token";

        // Mock validator to return success if called (which happens if regex matches loosely)
        $this->validator->method("validate")
            ->with($token)
            ->willReturn(["sub" => "123", "role" => "admin"]);

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withHeader("Authorization", "Bearer " . $token . " garbage");

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method("handle");

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testRejectsTokenPrefixedWithGarbage(): void
    {
        $token = "valid_token";
        $this->validator->method("validate")
            ->with($token)
            ->willReturn(["sub" => "123", "role" => "admin"]);

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/")
            ->withHeader("Authorization", "Garbage Bearer " . $token);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method("handle");

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(401, $response->getStatusCode());
    }
}
