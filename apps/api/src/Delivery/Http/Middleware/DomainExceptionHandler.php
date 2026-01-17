<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Exception\PasswordResetTokenExpiredException;
use App\Domain\Auth\Exception\PasswordResetTokenInvalidException;
use App\Domain\Auth\Exception\UserAlreadyExistsException;
use App\Domain\Auth\Exception\UserNotFoundException;
use App\Domain\Auth\Exception\WeakPasswordException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Throwable;

class DomainExceptionHandler
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $statusCode = $this->mapExceptionToStatusCode($exception);
        $message = $exception->getMessage();

        $response = $this->responseFactory->createResponse($statusCode);

        $error = [
            "message" => $message,
            "code" => $statusCode,
        ];

        if ($displayErrorDetails) {
            $error["trace"] = $exception->getTrace();
        }

        $payload = json_encode([
            "error" => $error,
        ], JSON_THROW_ON_ERROR);

        $response->getBody()->write($payload);

        return $response->withHeader("Content-Type", "application/json");
    }

    private function mapExceptionToStatusCode(Throwable $exception): int
    {
        return match (get_class($exception)) {
            InvalidCredentialsException::class,
            UserNotFoundException::class => 401,

            PasswordResetTokenExpiredException::class,
            PasswordResetTokenInvalidException::class,
            WeakPasswordException::class => 400,

            UserAlreadyExistsException::class => 409,

            default => $exception instanceof HttpException
            ? $exception->getCode()
            : 500,
        };
    }
}
