<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Auth;

use App\Application\Auth\Command\CreateUserCommand;
use App\Application\Auth\Handler\CreateUserHandler;
use App\Delivery\Http\Controller\Auth\CreateUserController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class CreateUserControllerTest extends TestCase
{
    private CreateUserHandler&MockObject $handler;
    private CreateUserController $controller;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(CreateUserHandler::class);
        $this->controller = new CreateUserController($this->handler);
    }

    public function testReturns201OnSuccess(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/users")
            ->withParsedBody(["email" => "new@example.com", "password" => "secret", "role" => "editor"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->expects($this->once())
            ->method("handle")
            ->with($this->callback(fn(CreateUserCommand $c) => $c->email === "new@example.com"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(201, $result->getStatusCode());
        $this->assertStringContainsString("User created", (string)$result->getBody());
    }

    public function testReturns400OnInvalidArgument(): void
    {
        // Email missing
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/users")
            ->withParsedBody(["password" => "secret"]);
        $response = (new ResponseFactory())->createResponse();

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertStringContainsString("Email and password are required", (string)$result->getBody());
    }

    public function testReturns409IfUserExists(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/users")
            ->withParsedBody(["email" => "exists@example.com", "password" => "secret"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->method("handle")
            ->willThrowException(new \Exception("User already exists"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(409, $result->getStatusCode());
        $this->assertStringContainsString("User already exists", (string)$result->getBody());
    }

    public function testReturns500OnOtherError(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/users")
            ->withParsedBody(["email" => "new@example.com", "password" => "secret"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->method("handle")
            ->willThrowException(new \Exception("DB Error"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(500, $result->getStatusCode());
    }
}
