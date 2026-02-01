<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Auth;

use App\Application\Auth\Handler\ConfirmPasswordResetHandler;
use App\Delivery\Http\Request\Auth\ConfirmPasswordResetRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConfirmPasswordResetController
{
    public function __construct(
        private readonly ConfirmPasswordResetHandler $handler,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $command = ConfirmPasswordResetRequest::fromPsr7($request);
        $this->handler->handle($command);

        $response->getBody()->write(json_encode([], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus(200);
    }
}
