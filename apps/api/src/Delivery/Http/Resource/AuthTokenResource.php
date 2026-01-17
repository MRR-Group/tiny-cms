<?php

declare(strict_types=1);

namespace App\Delivery\Http\Resource;

use App\Application\Auth\DTO\AuthTokenView;

class AuthTokenResource
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(AuthTokenView $view): array
    {
        return [
            "token" => $view->token,
            "expires_in" => $view->expiresIn,
            "type" => "Bearer",
        ];
    }
}
