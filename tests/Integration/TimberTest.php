<?php

declare(strict_types=1);

namespace n5s\TimberWebLink\Tests\Integration;

use Mantle\Testkit\TestCase;
use Psr\Link\LinkInterface;
use Timber\Timber;

use function n5s\TimberWebLink\get_extension;

final class TimberTest extends TestCase
{
    public function testHooksAreRegisteredBeforeWordPressLoads(): void
    {
        $this->assertSame(10, \has_filter('timber/twig', 'n5s\TimberWebLink\add_weblink_extension'));
        $this->assertSame(\PHP_INT_MAX, \has_action('wp_footer', 'n5s\TimberWebLink\send_headers'));
    }

    public function testTemplatesShareTheLinks(): void
    {
        // Timber creates a Twig environment for each compiled template.
        $this->assertSame('/first.css', Timber::compile_string("{{ preload('/first.css', {as: 'style'}) }}"));
        $this->assertSame('/second.js', Timber::compile_string("{{ preload('/second.js', {as: 'script'}) }}"));

        $hrefs = \array_map(
            static fn (LinkInterface $link): string => $link->getHref(),
            get_extension()->getLinksByRel('preload')
        );
        $this->assertContains('/first.css', $hrefs);
        $this->assertContains('/second.js', $hrefs);
    }
}
