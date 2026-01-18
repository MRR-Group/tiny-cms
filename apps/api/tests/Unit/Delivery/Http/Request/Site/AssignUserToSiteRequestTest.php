<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Request\Site;

use App\Delivery\Http\Request\Site\AssignUserToSiteRequest;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class AssignUserToSiteRequestTest extends TestCase
{
    public function testFromPsr7ReturnsCommand(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/admin/sites/assign')
            ->withParsedBody([
                'userId' => 'user-uuid',
                'siteId' => 'site-uuid',
            ]);

        $command = AssignUserToSiteRequest::fromPsr7($request);

        $this->assertEquals('user-uuid', $command->userId);
        $this->assertEquals('site-uuid', $command->siteId);
    }

    public function testFromPsr7ThrowsOnInvalidBodyType(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/admin/sites/assign')
            ->withParsedBody(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid body');

        AssignUserToSiteRequest::fromPsr7($request);
    }

    public function testFromPsr7ThrowsOnMissingFields(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/admin/sites/assign')
            ->withParsedBody([
                'userId' => 'user-uuid',
                // missing siteId
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: userId, siteId');

        AssignUserToSiteRequest::fromPsr7($request);
    }
}
