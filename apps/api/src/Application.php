<?php

declare(strict_types=1);

namespace App;

use App\Action\HealthAction;
use DI\Container;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;

final class Application
{
    public static function create(): SlimApp
    {
        $container = new Container();
        AppFactory::setContainer($container);

        $app = AppFactory::create();

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
    }
}
