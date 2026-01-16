<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Auth;

use App\Application\Auth\Handler\ChangePasswordHandler;
use App\Delivery\Http\Controller\Auth\ChangePasswordController;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class ChangePasswordControllerTest extends TestCase
{
    public function testReturns200OnSuccess(): void
    {
        $handler = $this->createMock(ChangePasswordHandler::class);
        $handler->expects($this->once())->method('handle');

        $controller = new ChangePasswordController($handler);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('user_id')->willReturn('00000000-0000-0000-0000-000000000000');
        $request->method('getParsedBody')->willReturn([
            'old_password' => 'old',
            'new_password' => 'new'
        ]);

        $response = new Response();
        $result = $controller($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
    }
}
