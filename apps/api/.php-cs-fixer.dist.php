<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$paths = [__DIR__ . '/src', __DIR__ . '/tests'];

if (class_exists(Blumilk\Codestyle\Config\ConfigBuilder::class)) {
    return (new Blumilk\Codestyle\Config\ConfigBuilder())
        ->in($paths)
        ->create();
}

$finder = Finder::create()->in($paths);

return (new Config())
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'strict_param' => true,
        'declare_strict_types' => true,
    ]);
