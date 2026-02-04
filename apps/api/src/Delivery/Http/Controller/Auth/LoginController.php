<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Auth;

use App\Application\Auth\Handler\LoginHandler;
use App\Delivery\Http\Request\Auth\LoginRequest;
use App\Delivery\Http\Resource\AuthTokenResource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController
{
    public function __construct(
        private readonly LoginHandler $handler,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = LoginRequest::fromPsr7($request);
            $tokenView = $this->handler->handle($command);
            $data = AuthTokenResource::toArray($tokenView);

            $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                "error" => [
                    "message" => $e->getMessage(),
                    "code" => 400,
                ],
            ], JSON_THROW_ON_ERROR));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(400);
        } catch (\Exception $e) {
            $status = in_array($e->getMessage(), ["Invalid credentials", "Invalid credentials provided", "User not found"], true) ? 401 : 500;
            $response->getBody()->write(json_encode([
                "error" => [
                    "message" => $e->getMessage(),
                    "code" => $status,
                ],
            ], JSON_THROW_ON_ERROR));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus($status);
        }
    }
}
