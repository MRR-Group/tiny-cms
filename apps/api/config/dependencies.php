<?php

declare(strict_types=1);

use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Application\Auth\Contract\TokenIssuerInterface;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineUserRepository;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineSiteRepository;
use App\Infrastructure\Security\Argon2PasswordHasher;
use App\Infrastructure\Security\JwtTokenService;
use Doctrine\ORM\EntityManagerInterface;
use function DI\autowire;
use function DI\factory;

return [
    EntityManagerInterface::class => function (Psr\Container\ContainerInterface $c) {
        return EntityManagerFactory::create($c->get('settings'));
    },
    UserRepositoryInterface::class => autowire(DoctrineUserRepository::class),
    SiteRepositoryInterface::class => autowire(DoctrineSiteRepository::class),
    PasswordHasherInterface::class => autowire(Argon2PasswordHasher::class),
    TokenIssuerInterface::class => function (Psr\Container\ContainerInterface $c) {
        return new JwtTokenService(
            $c->get(App\Domain\Shared\Clock\ClockInterface::class),
            $c->get('settings')['jwt_secret']
        );
    },
    App\Application\Auth\Contract\TokenValidatorInterface::class => function (Psr\Container\ContainerInterface $c) {
        return $c->get(TokenIssuerInterface::class);
    },
    Psr\Http\Message\ResponseFactoryInterface::class => autowire(Slim\Psr7\Factory\ResponseFactory::class),
    App\Domain\Shared\Clock\ClockInterface::class => autowire(App\Infrastructure\Shared\Clock\SystemClock::class),
    Symfony\Component\Mailer\MailerInterface::class => function (Psr\Container\ContainerInterface $c) {
        $settings = $c->get('settings');
        $dsn = $settings['mail']['dsn'] ?? 'null://null';
        $transport = Symfony\Component\Mailer\Transport::fromDsn($dsn);
        return new Symfony\Component\Mailer\Mailer($transport);
    },
    App\Domain\Auth\Service\EmailSenderInterface::class => function (Psr\Container\ContainerInterface $c) {
        $mailer = $c->get(Symfony\Component\Mailer\MailerInterface::class);
        $settings = $c->get('settings');
        $frontendUrl = $settings['frontend_url'] ?? 'http://localhost:5173';
        $senderEmail = $settings['mail']['from_address'] ?? 'no-reply@tinycms.com';
        return new App\Infrastructure\Communication\SymfonyMailerEmailSender($mailer, $frontendUrl, $senderEmail);
    },
];
