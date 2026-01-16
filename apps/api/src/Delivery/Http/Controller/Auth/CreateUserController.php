<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Auth;

use App\Application\Auth\Handler\CreateUserHandler;
use App\Delivery\Http\Request\Auth\CreateUserRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CreateUserController
{
    public function __construct(
        private readonly CreateUserHandler $handler
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = CreateUserRequest::fromPsr7($request);
            $this->handler->handle($command);

            $response->getBody()->write(json_encode(['message' => 'User created']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (\Exception $e) {
            // Differentiate business exception vs server error
            $status = $e->getMessage() === 'User already exists' ? 409 : 500;
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }
    }
}
