<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Service\VersionService;
use PHPUnit\Framework\TestCase;

final class VersionServiceTest extends TestCase
{
    private VersionService $service;

    protected function setUp(): void
    {
        $this->service = new VersionService();
    }

    public function testGetVersionReturnsString(): void
    {
        $version = $this->service->getVersion();

        $this->assertIsString($version);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version);
    }

    public function testGetMajorVersionReturnsInteger(): void
    {
        $major = $this->service->getMajorVersion();

        $this->assertIsInt($major);
        $this->assertGreaterThanOrEqual(0, $major);
    }

    public function testGetMinorVersionReturnsInteger(): void
    {
        $minor = $this->service->getMinorVersion();

        $this->assertIsInt($minor);
        $this->assertGreaterThanOrEqual(0, $minor);
    }

    public function testGetPatchVersionReturnsInteger(): void
    {
        $patch = $this->service->getPatchVersion();

        $this->assertIsInt($patch);
        $this->assertGreaterThanOrEqual(0, $patch);
    }

    public function testVersionPartsMatchFullVersion(): void
    {
        $fullVersion = $this->service->getVersion();
        $major = $this->service->getMajorVersion();
        $minor = $this->service->getMinorVersion();
        $patch = $this->service->getPatchVersion();

        $reconstructed = sprintf('%d.%d.%d', $major, $minor, $patch);

        $this->assertEquals($fullVersion, $reconstructed);
    }
}
