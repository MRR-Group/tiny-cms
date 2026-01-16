<?php

declare(strict_types=1);

namespace App;

use App\Action\HealthAction;
use App\Delivery\Http\Controller\Auth\ChangePasswordController;
use App\Delivery\Http\Controller\Auth\CreateUserController;
use App\Delivery\Http\Controller\Auth\LoginController;
use App\Delivery\Http\Middleware\JwtAuthMiddleware;
use App\Delivery\Http\Middleware\RoleMiddleware;
use DI\ContainerBuilder;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

final class Application
{
    public static function create(): SlimApp
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . "/../config/dependencies.php");
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

        $app->post("/auth/login", LoginController::class);
        $app->post("/auth/change-password", ChangePasswordController::class)
            ->add(JwtAuthMiddleware::class);

        $app->group("/admin", function (RouteCollectorProxy $group): void {
            $group->post("/users", CreateUserController::class);
        })->add(new RoleMiddleware("admin"))
            ->add(JwtAuthMiddleware::class);
    }
}
