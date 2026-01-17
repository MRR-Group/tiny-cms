<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Auth;

use App\Application\Auth\Command\ConfirmPasswordResetCommand;
use App\Application\Auth\Handler\ConfirmPasswordResetHandler;
use App\Delivery\Http\Controller\Auth\ConfirmPasswordResetController;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class ConfirmPasswordResetControllerTest extends TestCase
{
    public function testInvokeCallsHandlerAndReturns200(): void
    {
        $handler = $this->createMock(ConfirmPasswordResetHandler::class);
        $handler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(ConfirmPasswordResetCommand::class));

        $controller = new ConfirmPasswordResetController($handler);

        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/password-reset/confirm")
            ->withHeader("Content-Type", "application/json")
            ->withParsedBody(["token" => "token", "password" => "new-pass"]);

        $response = (new ResponseFactory())->createResponse();

        $result = $controller($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("application/json", $result->getHeaderLine("Content-Type"));
        $this->assertEquals("[]", (string)$result->getBody());
    }
}
