<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Auth;

use App\Application\Auth\Command\RequestPasswordResetCommand;
use App\Application\Auth\Handler\RequestPasswordResetHandler;
use App\Delivery\Http\Controller\Auth\RequestPasswordResetController;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class RequestPasswordResetControllerTest extends TestCase
{
    public function testInvokeCallsHandlerAndReturns200(): void
    {
        $handler = $this->createMock(RequestPasswordResetHandler::class);
        $handler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(RequestPasswordResetCommand::class));

        $controller = new RequestPasswordResetController($handler);

        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/password-reset/request")
            ->withHeader("Content-Type", "application/json")
            ->withParsedBody(["email" => "test@example.com"]);

        $response = (new ResponseFactory())->createResponse();

        $result = $controller($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("application/json", $result->getHeaderLine("Content-Type"));
        $this->assertEquals("[]", (string)$result->getBody());
    }
}
