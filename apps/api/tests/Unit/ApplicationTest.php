<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ServerRequestFactory;

class ApplicationTest extends TestCase
{
    public function testApplicationHandlesErrorsWithJson(): void
    {
        // Set necessary ENV vars to avoid connection errors if EntityManager tries to init
        $_ENV["JWT_SECRET"] = "test_secret_longer_than_32_chars_here";
        $_ENV["APP_ENV"] = "test";
        $_ENV["DB_HOST"] = "localhost"; // Dummy

        $app = Application::create();

        // Request a non-existent route to trigger 404
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/non-existent-route");

        $response = $app->handle($request);

        // Expect 404
        $this->assertEquals(404, $response->getStatusCode());

        // Expect JSON content type (DomainExceptionHandler sets this)
        // If Default Error Handler (Slim's default) was used (Mutant), it would be text/html or application/json depending on content negotiation,
        // but explicit handler forces our format.
        // Let's check body content structure if possible.
        // Slim default 404 in JSON mode returns: {"message":"Not found"}
        // Our DomainExceptionHandler returns: {"error": {"message": "...", "code": 404}} (Based on code reading)

        $body = (string)$response->getBody();
        $this->assertJson($body);
        $data = json_decode($body, true);

        // Check for our custom error structure
        $this->assertArrayHasKey("error", $data);
        $this->assertArrayHasKey("message", $data["error"]);

        // Since displayErrorDetails is true in Application configuration, we expect trace
        $this->assertArrayHasKey("trace", $data["error"]);

        // Slim default error handler usually returns flat JSON if Accept header is set, but Application::create doesn't force Accept header here.
        // Without Accept header, Slim default returns HTML.
        // So checking Content-Type is application/json is good enough proof validation logic kicked in?
        // Let's verify Content-Type.

        $this->assertEquals("application/json", $response->getHeaderLine("Content-Type"));
    }

    public function testErrorMiddlewareConfiguration(): void
    {
        $_ENV["JWT_SECRET"] = "test_secret";
        $_ENV["APP_ENV"] = "test";
        $_ENV["DB_HOST"] = "localhost";

        $app = Application::create();

        // Inspect MiddlewareDispatcher
        $reflection = new \ReflectionClass($app);
        $dispatcherProp = $reflection->getProperty("middlewareDispatcher");
        $dispatcherProp->setAccessible(true);
        $dispatcher = $dispatcherProp->getValue($app);

        // Inspect middleware stack
        // Slim stores them in a queue or we can look at "stack" if available,
        // usually MiddlewareDispatcher holds the tip of the stack.
        // But Slim 4 implementation is intricate.
        // Let's try to find ErrorMiddleware by searching.

        // Wait, MiddlewareDispatcher doesn't expose the stack easily.
        // But addErrorMiddleware returns the instance!
        // But Application::create drops it.

        // Let's try to assume that the error middleware is in the stack.
        // Getting access to the queue inside MiddlewareDispatcher might be needed.
        // MiddlewareDispatcher has protected $middleware array?
        // Let's check MiddlewareDispatcher definition (I can view it or guess).
        // It uses $middlewareStack usually.
        // Actually, let's use reflection on $dispatcher.

        $dispatcherReflection = new \ReflectionClass($dispatcher);
        $tipProp = $dispatcherReflection->getProperty("tip");
        $tipProp->setAccessible(true);
        $tip = $tipProp->getValue($dispatcher);

        // Traverse the linked list of middleware wrappers
        $foundMiddleware = null;
        $current = $tip;

        while ($current) {
            // Check if current is the anonymous wrapper class created by Slim
            // We can try to get 'middleware' property
            try {
                $ref = new \ReflectionClass($current);

                if (!$ref->hasProperty("middleware")) {
                    // Might be the Kernel (RouteRunner) or something else at the bottom
                    break;
                }

                $mwProp = $ref->getProperty("middleware");
                $mwProp->setAccessible(true);
                $mw = $mwProp->getValue($current);

                if ($mw instanceof ErrorMiddleware) {
                    $foundMiddleware = $mw;

                    break;
                }

                // Move to next
                if ($ref->hasProperty("next")) {
                    $nextProp = $ref->getProperty("next");
                    $nextProp->setAccessible(true);
                    $current = $nextProp->getValue($current);
                } else {
                    break;
                }
            } catch (\ReflectionException $e) {
                break;
            }
        }

        $this->assertNotNull($foundMiddleware, "ErrorMiddleware not found in stack");

        // Assert configuration
        $mwRefl = new \ReflectionClass($foundMiddleware);

        $logErrors = $mwRefl->getProperty("logErrors");
        $logErrors->setAccessible(true);
        $this->assertTrue($logErrors->getValue($foundMiddleware), "logErrors should be true");

        $logErrorDetails = $mwRefl->getProperty("logErrorDetails");
        $logErrorDetails->setAccessible(true);
        $this->assertTrue($logErrorDetails->getValue($foundMiddleware), "logErrorDetails should be true");
    }
}
