<?php

declare(strict_types=1);

use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Application\Auth\Contract\TokenIssuerInterface;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineUserRepository;
use App\Infrastructure\Security\Argon2PasswordHasher;
use App\Infrastructure\Security\JwtTokenService;
use Doctrine\ORM\EntityManagerInterface;
use function DI\autowire;
use function DI\factory;

return [
    EntityManagerInterface::class => factory([EntityManagerFactory::class, 'create']),
    UserRepositoryInterface::class => autowire(DoctrineUserRepository::class),
    PasswordHasherInterface::class => autowire(Argon2PasswordHasher::class),
    TokenIssuerInterface::class => autowire(JwtTokenService::class),
    App\Application\Auth\Contract\TokenValidatorInterface::class => autowire(JwtTokenService::class),
    Psr\Http\Message\ResponseFactoryInterface::class => autowire(Slim\Psr7\Factory\ResponseFactory::class),
];
