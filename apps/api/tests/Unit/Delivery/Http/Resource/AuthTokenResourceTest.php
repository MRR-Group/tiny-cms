<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Resource;

use App\Application\Auth\DTO\AuthTokenView;
use App\Delivery\Http\Resource\AuthTokenResource;
use PHPUnit\Framework\TestCase;

class AuthTokenResourceTest extends TestCase
{
    public function testConvertsToArray(): void
    {
        $view = new AuthTokenView("token_abc", 3600);
        $array = AuthTokenResource::toArray($view);

        $this->assertEquals([
            "token" => "token_abc",
            "expires_in" => 3600,
            "type" => "Bearer"
        ], $array);
    }

    public function testUsesDefaultExpiry(): void
    {
        $view = new AuthTokenView("token_def");
        $this->assertEquals(3600, $view->expiresIn);
    }
}
