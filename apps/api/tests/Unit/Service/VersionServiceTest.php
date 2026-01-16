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
        $this->service = new VersionService("1.2.3");
    }

    public function testGetVersionReturnsString(): void
    {
        $version = $this->service->getVersion();

        $this->assertIsString($version);
        $this->assertSame("1.2.3", $version);
    }

    public function testGetMajorVersionReturnsCorrectPart(): void
    {
        $major = $this->service->getMajorVersion();

        $this->assertSame(1, $major);
    }

    public function testGetMinorVersionReturnsCorrectPart(): void
    {
        $minor = $this->service->getMinorVersion();

        $this->assertSame(2, $minor);
    }

    public function testGetPatchVersionReturnsCorrectPart(): void
    {
        $patch = $this->service->getPatchVersion();

        $this->assertSame(3, $patch);
    }

    public function testVersionPartsMatchFullVersion(): void
    {
        $fullVersion = $this->service->getVersion();
        $major = $this->service->getMajorVersion();
        $minor = $this->service->getMinorVersion();
        $patch = $this->service->getPatchVersion();

        $reconstructed = sprintf("%d.%d.%d", $major, $minor, $patch);

        $this->assertSame($fullVersion, $reconstructed);
    }

    public function testDefaultVersionIsHandled(): void
    {
        $service = new VersionService();
        $this->assertSame("1.0.0", $service->getVersion());
        $this->assertSame(1, $service->getMajorVersion());
        $this->assertSame(0, $service->getMinorVersion());
        $this->assertSame(0, $service->getPatchVersion());
    }

    public function testShortVersionReturnsDefaults(): void
    {
        $service = new VersionService("1");

        $this->assertSame(1, $service->getMajorVersion());
        $this->assertSame(0, $service->getMinorVersion());
        $this->assertSame(0, $service->getPatchVersion());
    }
}
