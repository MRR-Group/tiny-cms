<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\ChangePasswordCommand;
use App\Delivery\Http\Request\Auth\ChangePasswordRequest;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class ChangePasswordRequestTest extends TestCase
{
    public function testCreatesCommandFromValidRequest(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/change-password")
            ->withAttribute("user_id", "550e8400-e29b-41d4-a716-446655440000")
            ->withParsedBody([
                "old_password" => "old",
                "new_password" => "new"
            ]);

        $command = ChangePasswordRequest::fromPsr7($request);

        $this->assertInstanceOf(ChangePasswordCommand::class, $command);
        $this->assertEquals("550e8400-e29b-41d4-a716-446655440000", $command->userId->toString());
        $this->assertEquals("old", $command->oldPassword);
        $this->assertEquals("new", $command->newPassword);
    }

    public function testConvertsObjectBodyToArray(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withAttribute("user_id", "550e8400-e29b-41d4-a716-446655440000")
            ->withParsedBody((object)["old_password" => "old", "new_password" => "new"]);
        
        $command = ChangePasswordRequest::fromPsr7($request);
        
        $this->assertEquals("old", $command->oldPassword);
        $this->assertEquals("new", $command->newPassword);
    }

    public function testConvertsNonStringInputsToString(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withAttribute("user_id", "550e8400-e29b-41d4-a716-446655440000")
            ->withParsedBody(["old_password" => 12345, "new_password" => 67890]);
        
        $command = ChangePasswordRequest::fromPsr7($request);
        
        $this->assertSame("12345", $command->oldPassword);
        $this->assertSame("67890", $command->newPassword);
    }

    public function testThrowsExceptionIfMissingFields(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/change-password")
            ->withParsedBody([
                "old_password" => "old",
                "new_password" => "new"
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required fields");

        ChangePasswordRequest::fromPsr7($request);
    }

    public function testThrowsExceptionIfOldPasswordMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/change-password")
            ->withAttribute("user_id", "user-123")
            ->withParsedBody([
                "new_password" => "new"
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required fields");

        ChangePasswordRequest::fromPsr7($request);
    }

    public function testThrowsExceptionIfNewPasswordMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/change-password")
            ->withAttribute("user_id", "user-123")
            ->withParsedBody([
                "old_password" => "old"
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required fields");

        ChangePasswordRequest::fromPsr7($request);
    }
}
