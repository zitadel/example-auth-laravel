<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$config = new PhpCsFixer\Config();
$config->setRules([
    '@PSR12' => true,
])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__)
        ->exclude('storage')
        ->exclude('bootstrap/cache')
        ->exclude('vendor'))
    ->setRiskyAllowed(true)
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__ . '/build/.php-cs-fixer.cache')
    ->setUsingCache(!(getenv('GITHUB_ACTIONS') === 'true'));

return $config;
