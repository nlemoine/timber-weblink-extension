<?php

/**
 * Front controller for the requests made with n5s/http-cli, on the WordPress installed by Mantle.
 */

declare(strict_types=1);

use n5s\WpHookKit\Hook;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;

use function pcov\start;

$rootDir = \dirname(__DIR__, 3);

// When PHPUnit collects code coverage, the test sends the file to write the coverage of this request to.
// PCOV starts before Composer's autoloader, which registers the hooks of src/functions.php.
$coverageFile = $_SERVER['HTTP_X_CODE_COVERAGE'] ?? null;
if (\is_string($coverageFile) && \extension_loaded('pcov')) {
    start();
} else {
    $coverageFile = null;
}

// Composer's autoloader is loaded before WordPress, as in Bedrock.
require "{$rootDir}/vendor/autoload.php";

if ($coverageFile !== null) {
    $filter = new Filter();
    $filter->includeFiles(\glob("{$rootDir}/src/*.php") ?: []);
    // Deprecated in php-code-coverage 14, but Selector::select() does not exist in 11, used by PHPUnit 11.
    $coverage = new CodeCoverage((new Selector())->forLineCoverage($filter), $filter);
    $coverage->start((string) $_SERVER['REQUEST_URI']);
    // Serialized as PHPUnit does for the tests it runs in a separate process: the test merges it into its own coverage.
    \register_shutdown_function(static function () use ($coverage, $coverageFile): void {
        $coverage->stop();
        \file_put_contents($coverageFile, \serialize($coverage));
    });
}

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
