<?php

declare(strict_types=1);

use function Mantle\Testing\manager;

// Composer's autoloader is loaded before WordPress, as in Bedrock: the hooks must be registered early.
require_once __DIR__ . '/../vendor/autoload.php';
$rootDir = realpath(__DIR__ . '/..');

// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
putenv("WP_CORE_DIR=$rootDir/tmp/wordpress");
putenv("CACHEDIR=$rootDir/tmp/test-cache");

manager()
    ->with_sqlite()
    ->install();
