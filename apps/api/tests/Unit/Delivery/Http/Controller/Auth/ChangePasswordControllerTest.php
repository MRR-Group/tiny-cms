<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Auth;

use App\Application\Auth\Command\ChangePasswordCommand;
use App\Application\Auth\Handler\ChangePasswordHandler;
use App\Delivery\Http\Controller\Auth\ChangePasswordController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class ChangePasswordControllerTest extends TestCase
{
    private ChangePasswordHandler&MockObject $handler;
    private ChangePasswordController $controller;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(ChangePasswordHandler::class);
        $this->controller = new ChangePasswordController($this->handler);
    }

    public function testReturns200OnSuccess(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/change-password")
            ->withAttribute("user_id", "550e8400-e29b-41d4-a716-446655440000")
            ->withParsedBody(["old_password" => "old", "new_password" => "new"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->expects($this->once())
            ->method("handle")
            ->with($this->callback(fn(ChangePasswordCommand $c) => $c->userId->toString() === "550e8400-e29b-41d4-a716-446655440000"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertStringContainsString("Password changed", (string)$result->getBody());
    }

    public function testReturns400IfInvalidOldPassword(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/change-password")
            ->withAttribute("user_id", "550e8400-e29b-41d4-a716-446655440000")
            ->withParsedBody(["old_password" => "wrong", "new_password" => "new"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->method("handle")
            ->willThrowException(new \Exception("Invalid old password"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertStringContainsString("Invalid old password", (string)$result->getBody());
    }

    public function testReturns500OnOtherErrors(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/change-password")
            ->withAttribute("user_id", "550e8400-e29b-41d4-a716-446655440000")
            ->withParsedBody(["old_password" => "old", "new_password" => "new"]);
        $response = (new ResponseFactory())->createResponse();

        $this->handler->method("handle")
            ->willThrowException(new \Exception("Unknown error"));

        $result = ($this->controller)($request, $response, []);

        $this->assertEquals(500, $result->getStatusCode());
    }
}
