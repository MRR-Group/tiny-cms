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

    public function testGetMajorVersionReturnsCorrectPart(): void
    {
        $version = $this->service->getVersion();
        $parts = explode(".", $version);
        $expectedMajor = (int)$parts[0];

        $major = $this->service->getMajorVersion();

        $this->assertSame($expectedMajor, $major);
    }

    public function testGetMinorVersionReturnsCorrectPart(): void
    {
        $version = $this->service->getVersion();
        $parts = explode(".", $version);
        $expectedMinor = (int)$parts[1];

        $minor = $this->service->getMinorVersion();

        $this->assertSame($expectedMinor, $minor);
    }

    public function testGetPatchVersionReturnsCorrectPart(): void
    {
        $version = $this->service->getVersion();
        $parts = explode(".", $version);
        $expectedPatch = (int)$parts[2];

        $patch = $this->service->getPatchVersion();

        $this->assertSame($expectedPatch, $patch);
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

    public function testMajorMinorPatchAreDifferentIndices(): void
    {
        $major = $this->service->getMajorVersion();
        $minor = $this->service->getMinorVersion();
        $patch = $this->service->getPatchVersion();

        // For version 1.0.0, parts are [1, 0, 0]
        // Major should be 1, minor and patch should be 0
        $this->assertSame(1, $major);
        $this->assertSame(0, $minor);
        $this->assertSame(0, $patch);
    }
}
