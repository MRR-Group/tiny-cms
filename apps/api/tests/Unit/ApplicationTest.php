<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class ApplicationTest extends TestCase
{
    public function testApplicationHandlesErrorsWithJson(): void
    {
        // Set necessary ENV vars to avoid connection errors if EntityManager tries to init
        $_ENV['JWT_SECRET'] = 'test_secret_longer_than_32_chars_here';
        $_ENV['APP_ENV'] = 'test';
        $_ENV['DB_HOST'] = 'localhost'; // Dummy

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

        $body = (string) $response->getBody();
        $this->assertJson($body);
        $data = json_decode($body, true);

        // Check for our custom error structure
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('message', $data['error']);

        // Since displayErrorDetails is true in Application configuration, we expect trace
        $this->assertArrayHasKey('trace', $data['error']);

        // Slim default error handler usually returns flat JSON if Accept header is set, but Application::create doesn't force Accept header here.
        // Without Accept header, Slim default returns HTML.
        // So checking Content-Type is application/json is good enough proof validation logic kicked in?
        // Let's verify Content-Type.

        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }
}
