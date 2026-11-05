<?php

/**
 * Front controller for the requests made with n5s/http-cli, on the WordPress installed by Mantle.
 */

declare(strict_types=1);

use n5s\WpHookKit\Hook;

$rootDir = \dirname(__DIR__, 3);

// Composer's autoloader is loaded before WordPress, as in Bedrock.
require "{$rootDir}/vendor/autoload.php";

// A copy of the test database, made by the test, so the request cannot lock or change it.
\define('DB_DIR', "{$rootDir}/tmp/http-cli/database/");
\define('DISABLE_WP_CRON', true);
\define('WP_USE_THEMES', true);

// Whatever the active theme, render the template of the test.
Hook::addFilter('template_include', static fn (): string => __DIR__ . '/' . \basename((string) ($_GET['template'] ?? '')) . '.php');

require "{$rootDir}/tmp/wordpress/wp-tests-config.php";
require ABSPATH . 'wp-settings.php';
\wp();
require ABSPATH . WPINC . '/template-loader.php';

// WordPress flushes the output buffers on shutdown, after n5s/http-cli collected the headers: flush them before.
\wp_ob_end_flush_all();
