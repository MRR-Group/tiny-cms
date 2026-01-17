<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Middleware;

use App\Delivery\Http\Middleware\DomainExceptionHandler;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Exception\PasswordResetTokenExpiredException;
use App\Domain\Auth\Exception\PasswordResetTokenInvalidException;
use App\Domain\Auth\Exception\UserAlreadyExistsException;
use App\Domain\Auth\Exception\UserNotFoundException;
use App\Domain\Auth\Exception\WeakPasswordException;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class DomainExceptionHandlerTest extends TestCase
{
    private DomainExceptionHandler $handler;

    protected function setUp(): void
    {
        $responseFactory = new ResponseFactory();
        $this->handler = new DomainExceptionHandler($responseFactory);
    }

    public function testHandlesUserNotFoundException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/");
        $exception = new UserNotFoundException(); // 401

        $response = ($this->handler)($request, $exception, true, true, true);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString("User not found", (string)$response->getBody());
    }

    public function testHandlesInvalidCredentialsException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/");
        $exception = new InvalidCredentialsException(); // 401

        $response = ($this->handler)($request, $exception, true, true, true);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testHandlesUserAlreadyExistsException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/");
        $exception = new UserAlreadyExistsException("test@example.com"); // 409

        $response = ($this->handler)($request, $exception, true, true, true);

        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testHandlesWeakPasswordException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/");
        $exception = new WeakPasswordException(); 
        // Note: WeakPasswordException constructor sets fixed message

        $response = ($this->handler)($request, $exception, true, true, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString("Password does not meet security requirements", (string)$response->getBody());
    }

    public function testHandlesTokenExceptions(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/");

        $exception1 = new PasswordResetTokenExpiredException(); 
        $response1 = ($this->handler)($request, $exception1, true, true, true);
        $this->assertEquals(400, $response1->getStatusCode());
        $this->assertStringContainsString("Password reset token has expired", (string)$response1->getBody());

        $exception2 = new PasswordResetTokenInvalidException(); 
        $response2 = ($this->handler)($request, $exception2, true, true, true);
        $this->assertEquals(400, $response2->getStatusCode());
        $this->assertStringContainsString("Password reset token is invalid", (string)$response2->getBody());
    }

    public function testReturns500ForUnknownException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/");
        $exception = new \RuntimeException("Unknown error");

        $response = ($this->handler)($request, $exception, true, true, true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString("Unknown error", (string)$response->getBody());
    }
}
