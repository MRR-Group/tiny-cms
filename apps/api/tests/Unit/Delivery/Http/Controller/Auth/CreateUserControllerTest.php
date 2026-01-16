<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Auth;

use App\Application\Auth\Handler\CreateUserHandler;
use App\Delivery\Http\Controller\Auth\CreateUserController;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class CreateUserControllerTest extends TestCase
{
    public function testReturns201OnSuccess(): void
    {
        $handler = $this->createMock(CreateUserHandler::class);
        $handler->expects($this->once())->method("handle");

        $controller = new CreateUserController($handler);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method("getParsedBody")->willReturn([
            "email" => "new@example.com",
            "password" => "secret",
            "role" => "editor",
        ]);

        $response = new Response();
        $result = $controller($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        $this->assertStringContainsString("User created", (string)$result->getBody());
    }

    public function testReturns400OnInvalidArgument(): void
    {
        $handler = $this->createMock(CreateUserHandler::class);
        $controller = new CreateUserController($handler);

        $request = $this->createMock(ServerRequestInterface::class);
        // Missing fields
        $request->method("getParsedBody")->willReturn([]);

        $response = new Response();
        $result = $controller($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
    }
}
