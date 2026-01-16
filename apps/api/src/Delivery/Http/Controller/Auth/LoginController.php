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

            $response->getBody()->write((string)json_encode($data));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write((string)json_encode(["error" => $e->getMessage()]));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(400);
        } catch (\Exception $e) {
            // Should differentiate 401 vs 500, but for now simple
            $status = $e->getMessage() === "Invalid credentials" ? 401 : 500;
            $response->getBody()->write((string)json_encode(["error" => $e->getMessage()]));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus($status);
        }
    }
}
