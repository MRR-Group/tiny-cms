<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\CreateUserCommand;
use App\Delivery\Http\Request\Auth\CreateUserRequest;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class CreateUserRequestTest extends TestCase
{
    public function testCreatesCommandFromValidRequest(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/users")
            ->withParsedBody([
                "email" => "new@example.com",
                "password" => "secret",
                "role" => "admin"
            ]);

        $command = CreateUserRequest::fromPsr7($request);

        $this->assertInstanceOf(CreateUserCommand::class, $command);
        $this->assertEquals("new@example.com", $command->email);
        $this->assertEquals("secret", $command->password);
        $this->assertEquals("admin", $command->role);
    }

    public function testUsesDefaultRoleIfMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/users")
            ->withParsedBody([
                "email" => "new@example.com",
                "password" => "secret"
            ]);

        $command = CreateUserRequest::fromPsr7($request);

        $this->assertEquals("editor", $command->role);
    }

    public function testConvertsObjectBodyToArray(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody((object)["email" => "test@test.com", "password" => "pass", "role" => "admin"]);
        
        $command = CreateUserRequest::fromPsr7($request);
        
        $this->assertEquals("test@test.com", $command->email);
        $this->assertEquals("pass", $command->password);
        $this->assertEquals("admin", $command->role);
    }

    public function testConvertsNonStringInputsToString(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody(["email" => 12345, "password" => 67890, "role" => 1]);
        
        $command = CreateUserRequest::fromPsr7($request);
        
        $this->assertSame("12345", $command->email);
        $this->assertSame("67890", $command->password);
        $this->assertSame("1", $command->role);
    }

    public function testThrowsExceptionIfEmailMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/users")
            ->withParsedBody([
                "password" => "secret"
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Email and password are required");

        CreateUserRequest::fromPsr7($request);
    }
}
