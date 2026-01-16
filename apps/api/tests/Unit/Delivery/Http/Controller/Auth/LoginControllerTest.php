<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Auth;

use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\DTO\AuthTokenView;
use App\Application\Auth\Handler\LoginHandler;
use App\Delivery\Http\Controller\Auth\LoginController;
use App\Delivery\Http\Request\Auth\LoginRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

class LoginControllerTest extends TestCase
{
    public function testReturnsTokenOnSuccess(): void
    {
        $handler = $this->createMock(LoginHandler::class);
        $handler->method('handle')->willReturn(new AuthTokenView('jwt_token', 3600));

        $controller = new LoginController($handler);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'email' => 'test@example.com',
            'password' => 'secret'
        ]);

        $response = new Response();
        $result = $controller($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        $body = (string) $result->getBody();
        $this->assertStringContainsString('jwt_token', $body);
    }

    public function testReturns401OnFailure(): void
    {
        $handler = $this->createMock(LoginHandler::class);
        $handler->method('handle')->willThrowException(new \Exception('Invalid credentials'));

        $controller = new LoginController($handler);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'email' => 'test@example.com',
            'password' => 'wrong'
        ]);

        $response = new Response();
        $result = $controller($request, $response);

        $this->assertEquals(401, $result->getStatusCode());
    }
}
