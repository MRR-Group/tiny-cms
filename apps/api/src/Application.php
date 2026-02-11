<?php

declare(strict_types=1);

namespace App;

use App\Action\HealthAction;
use App\Delivery\Http\Controller\Admin\SiteController as AdminSiteController;
use App\Delivery\Http\Controller\Admin\UserController;
use App\Delivery\Http\Controller\Auth\ChangePasswordController;
use App\Delivery\Http\Controller\Auth\ConfirmPasswordResetController;
use App\Delivery\Http\Controller\Auth\CreateUserController;
use App\Delivery\Http\Controller\Auth\LoginController;
use App\Delivery\Http\Controller\Auth\RequestPasswordResetController;
use App\Delivery\Http\Controller\User\SiteController as UserSiteController;
use App\Delivery\Http\Middleware\DomainExceptionHandler;
use App\Delivery\Http\Middleware\JwtAuthMiddleware;
use App\Delivery\Http\Middleware\RoleMiddleware;
use App\Delivery\Http\Middleware\SiteAccessMiddleware;
use DI\ContainerBuilder;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

final class Application
{
    public static function create(): SlimApp
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . "/../config/settings.php");
        $containerBuilder->addDefinitions(__DIR__ . "/../config/dependencies.php");
        $container = $containerBuilder->build();

        AppFactory::setContainer($container);

        $app = AppFactory::create();

        $app->addBodyParsingMiddleware();

        self::registerRoutes($app);

        $settings = $container->get("settings");

        $errorMiddleware = $app->addErrorMiddleware(
            displayErrorDetails: $settings["displayErrorDetails"],
            logErrors: $settings["logErrors"],
            logErrorDetails: $settings["logErrorDetails"],
        );

        $domainExceptionHandler = $container->get(DomainExceptionHandler::class);
        $errorMiddleware->setDefaultErrorHandler($domainExceptionHandler);

        return $app;
    }

    private static function registerRoutes(SlimApp $app): void
    {
        $app->get("/health", HealthAction::class);

        $app->post("/auth/login", LoginController::class);
        $app->post("/auth/change-password", ChangePasswordController::class)
            ->add(JwtAuthMiddleware::class);
        $app->post("/auth/password-reset/request", RequestPasswordResetController::class);
        $app->post("/auth/password-reset/confirm", ConfirmPasswordResetController::class);

        $app->group("/admin", function (RouteCollectorProxy $group): void {
            $group->post("/users", CreateUserController::class);
            $group->get("/users", [UserController::class, "list"]);
            $group->post("/sites", [AdminSiteController::class, "create"]);
            $group->get("/sites", [AdminSiteController::class, "list"]);
            $group->get("/sites/{id}", [AdminSiteController::class, "get"]);
            $group->put("/sites/{id}", [AdminSiteController::class, "update"]);
            $group->delete("/sites/{id}", [AdminSiteController::class, "delete"]);
            $group->post("/sites/{id}/sections", [AdminSiteController::class, "createSection"]);
            $group->put("/sites/{id}/sections/order", [AdminSiteController::class, "reorderSections"]);
            $group->put("/sites/{id}/sections/{sectionId}", [AdminSiteController::class, "updateSection"]);
            $group->delete("/sites/{id}/sections/{sectionId}", [AdminSiteController::class, "deleteSection"]);
            $group->post("/sites/assign", [AdminSiteController::class, "assignUser"]);
            $group->delete("/sites/{id}/users/{userId}", [AdminSiteController::class, "unassignUser"]);
        })->add(new RoleMiddleware("admin"))
            ->add(JwtAuthMiddleware::class);

        $app->get("/sites/{id}/sections", [AdminSiteController::class, "listSections"])
            ->add(SiteAccessMiddleware::class)
            ->add(JwtAuthMiddleware::class);
        $app->get("/sites/{id}/sections/{sectionId}/items", [AdminSiteController::class, "listSectionItems"])
            ->add(SiteAccessMiddleware::class)
            ->add(JwtAuthMiddleware::class);
        $app->post("/sites/{id}/sections/{sectionId}/items", [AdminSiteController::class, "createSectionItem"])
            ->add(SiteAccessMiddleware::class)
            ->add(JwtAuthMiddleware::class);
        $app->put("/sites/{id}/sections/{sectionId}/items/{itemId}", [AdminSiteController::class, "updateSectionItem"])
            ->add(SiteAccessMiddleware::class)
            ->add(JwtAuthMiddleware::class);
        $app->delete("/sites/{id}/sections/{sectionId}/items/{itemId}", [AdminSiteController::class, "deleteSectionItem"])
            ->add(SiteAccessMiddleware::class)
            ->add(JwtAuthMiddleware::class);

        $app->get("/sites", [UserSiteController::class, "listAssigned"])
            ->add(JwtAuthMiddleware::class);
    }
}
