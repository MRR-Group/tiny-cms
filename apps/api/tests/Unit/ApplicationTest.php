<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Middleware\ErrorMiddleware;

final class ApplicationTest extends TestCase
{
    public function testApplicationHasErrorMiddlewareConfigured(): void
    {
        $app = Application::create();

        $middlewareDispatcher = $app->getMiddlewareDispatcher();

        $reflection = new \ReflectionClass($middlewareDispatcher);
        $tipProperty = $reflection->getProperty("tip");
        $tipProperty->setAccessible(true);
        $tip = $tipProperty->getValue($middlewareDispatcher);

        $tipReflection = new \ReflectionObject($tip);
        $middlewareProperty = $tipReflection->getProperty("middleware");
        $middlewareProperty->setAccessible(true);
        $middleware = $middlewareProperty->getValue($tip);

        $this->assertInstanceOf(ErrorMiddleware::class, $middleware, "The last added middleware (tip) should be ErrorMiddleware");

        // Verify configuration on ErrorMiddleware
        // ErrorMiddleware stores these settings as private properties
        $emReflection = new \ReflectionClass($middleware);

        $displayProp = $emReflection->getProperty("displayErrorDetails");
        $displayProp->setAccessible(true);
        $this->assertTrue($displayProp->getValue($middleware), "displayErrorDetails should be true");

        $logErrorsProp = $emReflection->getProperty("logErrors");
        $logErrorsProp->setAccessible(true);
        $this->assertTrue($logErrorsProp->getValue($middleware), "logErrors should be true");

        $logDetailsProp = $emReflection->getProperty("logErrorDetails");
        $logDetailsProp->setAccessible(true);
        $this->assertTrue($logDetailsProp->getValue($middleware), "logErrorDetails should be true");
    }
}
