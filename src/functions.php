<?php

declare(strict_types=1);

// The hook is registered next to its named callback.
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

namespace n5s\TimberWebLink;

use Twig\Environment;

/**
 * Adds the WebLink functions to a Twig environment.
 */
function add_weblink_extension(Environment $twig): Environment
{
    $twig->addExtension(new WebLinkExtension());

    return $twig;
}

if (\function_exists('add_filter')) {
    \add_filter('timber/twig', __NAMESPACE__ . '\\add_weblink_extension');
}
