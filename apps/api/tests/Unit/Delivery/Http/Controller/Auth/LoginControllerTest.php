<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Auth;

use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\DTO\AuthTokenView;
use App\Application\Auth\Handler\LoginHandler;
use App\Delivery\Http\Controller\Auth\LoginController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class LoginControllerTest extends TestCase
{
    private LoginHandler&MockObject $handler;
    private LoginController $controller;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(LoginHandler::class);
        $this->controller = new LoginController($this->handler);
    }

    public function testReturnsTokenOnSuccess(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody(["email" => "test@example.com", "password" => "secret"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->expects($this->once())
            ->method("handle")
            ->with($this->callback(fn(LoginCommand $c) => $c->email === "test@example.com"))
            ->willReturn(new AuthTokenView("jwt_token"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertEquals("jwt_token", $body["token"]);
    }

    public function testReturns401OnInvalidCredentials(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody(["email" => "test@example.com", "password" => "wrong"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->method("handle")
            ->willThrowException(new \Exception("Invalid credentials"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(401, $result->getStatusCode());
        $this->assertStringContainsString("Invalid credentials", (string)$result->getBody());
    }

    public function testReturns500OnOtherErrors(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody(["email" => "test@example.com", "password" => "secret"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->method("handle")
            ->willThrowException(new \Exception("Database error"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testReturns400OnInvalidArgument(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody([]); // Empty body triggers InvalidArgumentException in Request::fromPsr7
        $response = (new ResponseFactory())->createResponse();

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertStringContainsString("Email and password are required", (string)$result->getBody());
    }
}
