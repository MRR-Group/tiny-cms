<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config = new Blumilk\Codestyle\Config(
    finder: $finder,
);

return $config->config();
