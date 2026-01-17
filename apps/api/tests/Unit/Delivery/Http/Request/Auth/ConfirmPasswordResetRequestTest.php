<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Auth;

use App\Application\Auth\Command\ConfirmPasswordResetCommand;
use App\Delivery\Http\Request\Auth\ConfirmPasswordResetRequest;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class ConfirmPasswordResetRequestTest extends TestCase
{
    public function testFromPsr7WithValidData(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody(["token" => "token123", "password" => "newPassword"]);

        $command = ConfirmPasswordResetRequest::fromPsr7($request);

        $this->assertInstanceOf(ConfirmPasswordResetCommand::class, $command);
        $this->assertSame("token123", $command->token);
        $this->assertSame("newPassword", $command->password);
    }

    public function testFromPsr7ThrowIfTokenMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody(["password" => "pass"]);

        $this->expectException(\InvalidArgumentException::class);
        ConfirmPasswordResetRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowIfPasswordMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody(["token" => "t"]);

        $this->expectException(\InvalidArgumentException::class);
        ConfirmPasswordResetRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowIfBothMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody([]);

        $this->expectException(\InvalidArgumentException::class);
        ConfirmPasswordResetRequest::fromPsr7($request);
    }

    public function testFromPsr7HandlesNullBodySafely(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/");
        // Body is null

        $this->expectException(\InvalidArgumentException::class);
        ConfirmPasswordResetRequest::fromPsr7($request);
    }

    public function testFromPsr7CastsInputsToString(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody(["token" => 123, "password" => 456]);

        $command = ConfirmPasswordResetRequest::fromPsr7($request);

        $this->assertSame("123", $command->token);
        $this->assertSame("456", $command->password);
    }

    public function testFromPsr7WithObjectBodyHandlesCast(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody((object)["token" => "token", "password" => "pass"]);

        $command = ConfirmPasswordResetRequest::fromPsr7($request);

        $this->assertSame("token", $command->token);
        $this->assertSame("pass", $command->password);
    }
}
