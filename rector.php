<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withCache(__DIR__ . '/tmp/rector')
    // PHP version comes from the "php" constraint in composer.json, so the library stays PHP 8.2 compatible.
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        instanceOf: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
    )
    // PHPUnit migration rules, bound to the PHPUnit version in composer.lock.
    ->withComposerBased(phpunit: true)
    // WordPress signatures, so type inference does not go blind on their APIs.
    ->withPhpstanConfigs([__DIR__ . '/phpstan.neon.dist'])
    ->withImportNames(importShortClasses: false, removeUnusedImports: true);
