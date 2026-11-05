<?php

declare(strict_types=1);

namespace n5s\TimberWebLink\Tests\Integration;

use Mantle\Testkit\TestCase;
use n5s\TimberWebLink\WebLinkExtension;
use Psr\Link\LinkInterface;
use Timber\Timber;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

use function n5s\TimberWebLink\add_weblink_extension;
use function n5s\TimberWebLink\get_extension;

final class TimberTest extends TestCase
{
    public function testHooksAreRegisteredBeforeWordPressLoads(): void
    {
        $this->assertSame(10, \has_filter('timber/twig', 'n5s\TimberWebLink\add_weblink_extension'));
        $this->assertSame(10, \has_action('wp_finalized_template_enhancement_output_buffer', 'n5s\TimberWebLink\send_headers'));
        $this->assertFalse(\has_action('wp_footer', 'n5s\TimberWebLink\send_headers'));
    }

    public function testTheTemplateOutputBufferIsStarted(): void
    {
        // Since WordPress 6.9, classic themes start it anyway: leave only the action added by this library.
        \remove_filter('wp_should_output_buffer_template_for_enhancement', '__return_true', 0);

        $this->assertTrue(\wp_should_output_buffer_template_for_enhancement());
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

    public function testTheExtensionIsAddedOnceToAnEnvironment(): void
    {
        $twig = new Environment(new ArrayLoader());
        // For example when a theme also adds the shared extension itself.
        $twig->addExtension(get_extension());

        $this->assertSame($twig, add_weblink_extension($twig));
        $this->assertSame(get_extension(), $twig->getExtension(WebLinkExtension::class));
    }
}
