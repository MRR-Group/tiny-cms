<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$paths = [__DIR__ . '/src', __DIR__ . '/tests'];

if (class_exists(Blumilk\Codestyle\Config\ConfigBuilder::class)) {
    $builder = new Blumilk\Codestyle\Config\ConfigBuilder();

    if (method_exists($builder, 'in')) {
        $builder = $builder->in($paths);
    }

    if (method_exists($builder, 'create')) {
        return $builder->create();
    }
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
