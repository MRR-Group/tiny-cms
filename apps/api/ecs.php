<?php

declare(strict_types=1);

use Blumilk\Codestyle\Config\BlumilkConfig;

return BlumilkConfig::create()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
