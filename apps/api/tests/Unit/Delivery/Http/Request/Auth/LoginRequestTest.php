<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\LoginCommand;
use App\Delivery\Http\Request\Auth\LoginRequest;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class LoginRequestTest extends TestCase
{
    public function testCreatesCommandFromValidRequest(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody([
                "email" => "test@example.com",
                "password" => "secret",
            ]);

        $command = LoginRequest::fromPsr7($request);

        $this->assertInstanceOf(LoginCommand::class, $command);
        $this->assertEquals("test@example.com", $command->email);
        $this->assertEquals("secret", $command->password);
    }

    public function testConvertsObjectBodyToArray(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody((object)["email" => "test@test.com", "password" => "pass"]);

        $command = LoginRequest::fromPsr7($request);

        $this->assertEquals("test@test.com", $command->email);
        $this->assertEquals("pass", $command->password);
    }

    public function testConvertsNonStringInputsToString(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody(["email" => 12345, "password" => 67890]);

        $command = LoginRequest::fromPsr7($request);

        $this->assertSame("12345", $command->email);
        $this->assertSame("67890", $command->password);
    }

    public function testThrowsExceptionIfEmailMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody([
                "password" => "secret",
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Email and password are required");

        LoginRequest::fromPsr7($request);
    }

    public function testThrowsExceptionIfPasswordMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody([
                "email" => "test@example.com",
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Email and password are required");

        LoginRequest::fromPsr7($request);
    }

    public function testThrowsExceptionIfBothMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Email and password are required");

        LoginRequest::fromPsr7($request);
    }

    public function testHandlesEmptyStringsAsMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withParsedBody([
                "email" => "",
                "password" => "",
            ]);

        $this->expectException(\InvalidArgumentException::class);
        LoginRequest::fromPsr7($request);
    }
}
