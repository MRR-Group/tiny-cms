<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Auth;

use App\Application\Auth\Handler\RequestPasswordResetHandler;
use App\Delivery\Http\Request\Auth\RequestPasswordResetRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestPasswordResetController
{
    public function __construct(
        private readonly RequestPasswordResetHandler $handler,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $command = RequestPasswordResetRequest::fromPsr7($request);
        $this->handler->handle($command);

        $response->getBody()->write(json_encode([], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus(200);
    }
}
