<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Auth;

use App\Application\Auth\Handler\ChangePasswordHandler;
use App\Delivery\Http\Request\Auth\ChangePasswordRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChangePasswordController
{
    public function __construct(
        private readonly ChangePasswordHandler $handler,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = ChangePasswordRequest::fromPsr7($request);
            $this->handler->handle($command);

            $response->getBody()->write(json_encode(["message" => "Password changed"], JSON_THROW_ON_ERROR));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(200);
        } catch (\Exception $e) {
            $status = $e->getMessage() === "Invalid old password" ? 400 : 500;
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus($status);
        }
    }
}
