<?php

declare(strict_types=1);

// The hooks are registered next to their named callbacks.
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

namespace n5s\TimberWebLink;

use n5s\WpHookKit\Hook;
use Symfony\Component\WebLink\HttpHeaderSerializer;
use Twig\Environment;

/**
 * The extension shared by every Twig environment: Timber creates one per compiled template.
 */
function get_extension(): WebLinkExtension
{
    static $extension = null;

    return $extension ??= new WebLinkExtension();
}

/**
 * Adds the WebLink functions to a Twig environment.
 */
function add_weblink_extension(Environment $twig): Environment
{
    // Twig throws if the same extension is added twice to an environment.
    if (!$twig->hasExtension(WebLinkExtension::class)) {
        $twig->addExtension(get_extension());
    }

    return $twig;
}

/**
 * Sends the links collected while rendering as a Link HTTP header.
 */
function send_headers(): void
{
    if (\headers_sent()) {
        return;
    }

    // Symfony documents the parameter as LinkInterface[]|\Traversable, which PHPStan narrows to Traversable.
    // @phpstan-ignore argument.type
    $header = (new HttpHeaderSerializer())->serialize(get_extension()->getLinks());
    if ($header === null) {
        return;
    }

    \header('Link: ' . $header, false);
}

// Named functions rather than closures, so they can be removed with remove_filter() and remove_action().
// Hook::addFilter() also works when Composer's autoloader is loaded before WordPress, as in Bedrock.
Hook::addFilter('timber/twig', __NAMESPACE__ . '\\add_weblink_extension');

// WordPress 6.9+. Fires once the template output is complete, before it is sent, so links added anywhere in the page
// are included. WordPress only starts the output buffer if this action has a callback when the template is included.
Hook::addAction('wp_finalized_template_enhancement_output_buffer', __NAMESPACE__ . '\\send_headers');
