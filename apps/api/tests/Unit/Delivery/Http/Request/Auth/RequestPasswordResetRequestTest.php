<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Auth;

use App\Delivery\Http\Request\Auth\RequestPasswordResetRequest;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class RequestPasswordResetRequestTest extends TestCase
{
    public function testFromPsr7WithValidData(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody(["email" => "test@example.com"]);

        $command = RequestPasswordResetRequest::fromPsr7($request);

        $this->assertSame("test@example.com", $command->email);
    }

    public function testFromPsr7WithMissingEmailThrowsException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Email is required");

        RequestPasswordResetRequest::fromPsr7($request);
    }

    public function testFromPsr7WithNullBodyThrowsExceptionAndDoesNotCrash(): void
    {
        // getParsedBody returns null by default if not set
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Email is required");

        RequestPasswordResetRequest::fromPsr7($request);
    }

    public function testFromPsr7CastsToEmailString(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody(["email" => 12345]);

        $command = RequestPasswordResetRequest::fromPsr7($request);

        $this->assertSame("12345", $command->email);
    }

    public function testFromPsr7WithObjectBodyHandlesCast(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/")
            ->withParsedBody((object)["email" => "test@example.com"]);

        // If cast is removed, this might fail or throw depending on exact implementation of mutant.
        // But (array)(object)['email'=>'x'] works.
        // $object['email'] fails.

        $command = RequestPasswordResetRequest::fromPsr7($request);
        $this->assertSame("test@example.com", $command->email);
    }
}
