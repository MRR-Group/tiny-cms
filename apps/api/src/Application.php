<?php

declare(strict_types=1);

namespace App;

use App\Action\HealthAction;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;

final class Application
{
    public static function create(): SlimApp
    {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../config/dependencies.php');
        $container = $containerBuilder->build();

        AppFactory::setContainer($container);


        $app = AppFactory::create();

        $app->addBodyParsingMiddleware();

        self::registerRoutes($app);

        $app->addErrorMiddleware(
            displayErrorDetails: true,
            logErrors: true,
            logErrorDetails: true,
        );

        return $app;
    }

    private static function registerRoutes(SlimApp $app): void
    {
        $app->get("/health", HealthAction::class);

        $app->post("/auth/login", \App\Delivery\Http\Controller\Auth\LoginController::class);
        $app->post("/auth/change-password", \App\Delivery\Http\Controller\Auth\ChangePasswordController::class)
            ->add(\App\Delivery\Http\Middleware\JwtAuthMiddleware::class);

        $app->group('/admin', function (\Slim\Routing\RouteCollectorProxy $group) {
            $group->post("/users", \App\Delivery\Http\Controller\Auth\CreateUserController::class);
        })->add(new \App\Delivery\Http\Middleware\RoleMiddleware('admin'))
            ->add(\App\Delivery\Http\Middleware\JwtAuthMiddleware::class);
    }
}
