<?php

declare(strict_types=1);

namespace n5s\TimberWebLink\Tests\Integration;

use n5s\HttpCli\Client;
use n5s\HttpCli\RequestOptions;
use n5s\HttpCli\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\CodeCoverage;

/**
 * Front-end requests in a separate PHP process, where n5s/http-cli records the headers.
 */
final class LinkHeaderTest extends TestCase
{
    private const REST_API_REL = 'rel="https://api.w.org/"';

    public function testTheLinkHeaderHasTheLinksOfThePage(): void
    {
        $response = $this->request('classic');

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(
            ['Link: </app.css>; rel="preload"; as="style",</late.js>; rel="preload"; as="script"'],
            $this->linkHeaders($response)
        );
    }

    public function testTheLinkHeaderOfWordPressIsKept(): void
    {
        $headers = $this->request('classic')->getHeaders();

        $this->assertNotEmpty(\array_filter(
            $headers,
            static fn (string $header): bool => \str_starts_with($header, 'Link:') && \str_contains($header, self::REST_API_REL)
        ));
    }

    public function testNoLinkHeaderWithoutLinks(): void
    {
        $response = $this->request('empty');

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertStringContainsString('<p>No links</p>', $response->getContent());
        $this->assertSame([], $this->linkHeaders($response));
    }

    public function testNoLinkHeaderWhenTheSiteTurnsOffTheBuffer(): void
    {
        $response = $this->request('classic', ['output_buffer' => 'off']);

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertStringContainsString('href="/app.css"', $response->getContent());
        $this->assertSame([], $this->linkHeaders($response));
    }

    /**
     * The Link headers, without the one WordPress sends for the REST API.
     *
     * @return list<string>
     */
    private function linkHeaders(Response $response): array
    {
        return \array_values(\array_filter(
            $response->getHeaders(),
            static fn (string $header): bool => \str_starts_with($header, 'Link:') && !\str_contains($header, self::REST_API_REL)
        ));
    }

    /**
     * @param array<string, string> $query
     */
    private function request(string $template, array $query = []): Response
    {
        $rootDir = \dirname(__DIR__, 2);
        $database = "{$rootDir}/tmp/http-cli/database";
        // The front controller uses this copy of the test database: see tests/fixtures/front/index.php.
        if (!\is_dir($database)) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- Test setup, WP_Filesystem is not loaded.
            \mkdir($database, 0777, true);
        }
        \copy("{$rootDir}/tmp/wordpress/wp-content/database/.ht.sqlite", "{$database}/.ht.sqlite");

        $coverage = CodeCoverage::instance();
        $coverageFile = "{$rootDir}/tmp/http-cli/coverage";
        $options = $coverage->isActive()
            ? RequestOptions::create()->header('X-Code-Coverage', $coverageFile)->build()
            : null;

        // Run from the root directory, where PCOV finds src/ when pcov.directory is not set.
        $response = (new Client($rootDir, 'tests/fixtures/front/index.php'))
            ->request('GET', 'http://example.org/?' . \http_build_query(['template' => $template] + $query), $options);

        if ($coverage->isActive() && \is_file($coverageFile)) {
            // As PHPUnit merges the coverage of the tests it runs in a separate process.
            $coverage->codeCoverage()->merge(\unserialize((string) \file_get_contents($coverageFile)));
            // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Test cleanup, as for mkdir() above.
            \unlink($coverageFile);
        }

        return $response;
    }
}
