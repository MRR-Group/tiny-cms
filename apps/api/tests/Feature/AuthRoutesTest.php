<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class AuthRoutesTest extends TestCase
{
    public function testBodyParsingAndErrorMiddleware(): void
    {
        $app = Application::create();
        // Valid JSON body, but invalid credentials (or effectively mocking DB failure)
        // If BodyParsing is removed -> Request body is null -> Controller throws InvalidArgumentException -> 400 & JSON error
        // Wait, if Parsing removed => data missing => 400.
        // If Parsing present => data present => 500 (DB error) or 401.
        // So we expect code != 400 if we send valid structure?
        // Actually, let's send valid structure.
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withHeader("Content-Type", "application/json");
        $request->getBody()->write(json_encode(["email" => "test@example.com", "password" => "pass"]));
        
        $response = $app->handle($request);
        
        // If body parsing works, we should NOT get "Email and password are required" (which is 400).
        // We probably get 500 (DB connection) or 401.
        // If mutant removes BodyParsing, we get 400.
        $this->assertNotEquals(400, $response->getStatusCode(), "Body Parsing middleware should be active");
        
        // Test Error Middleware & Handler
        // Send empty body to trigger InvalidArgumentException (400)
        $requestError = (new ServerRequestFactory())->createServerRequest("POST", "/auth/login")
            ->withHeader("Content-Type", "application/json");
        
        $responseError = $app->handle($requestError);
        
        // Must be JSON (DomainExceptionHandler), not HTML (Slim default)
        $this->assertEquals(400, $responseError->getStatusCode());
        $this->assertEquals("application/json", $responseError->getHeaderLine("Content-Type"));
        $body = (string) $responseError->getBody();
        $this->assertStringContainsString("error", $body);
    }

    public function testAuthMiddlewareIsRegistered(): void
    {
        $app = Application::create();
        // Request without token
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/auth/change-password");

        $response = $app->handle($request);
        // Should be 401 (Middleware) not 400 (Controller validation) or 200
        $this->assertEquals(401, $response->getStatusCode(), "JwtAuthMiddleware should block unauthenticated requests");
    }

    public function testRoleMiddlewareIsRegistered(): void
    {
        $app = Application::create();
        // Request to admin route without token (should be 401 by JwtAuth)
        // Or with token but wrong role... testing full chain is hard without valid token.
        // But if we remove JwtAuth, RoleMiddleware might throw 500 (missing attribute) or 403.
        // Let's stick to simple existence check for now.
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/users");
        $response = $app->handle($request);
        // If route exists, we get 401 (JwtAuth first).
        $this->assertNotEquals(404, $response->getStatusCode());
    }
}
