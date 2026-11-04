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
    $twig->addExtension(get_extension());

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

// Timber::render() prints the page once it is compiled: when the template calls wp_footer, headers can still be sent.
Hook::addAction('wp_footer', __NAMESPACE__ . '\\send_headers', \PHP_INT_MAX);
