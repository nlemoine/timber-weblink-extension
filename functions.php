<?php

namespace HelloNico\Timber;

use HelloNico\Twig\WebLinkExtension;

function add_weblink_extension($twig)
{
    $twig->addExtension(new WebLinkExtension());

    return $twig;
}

if (function_exists('add_filter')) {
    add_filter('timber/loader/twig', sprintf('%s\\add_weblink_extension', __NAMESPACE__));
}
