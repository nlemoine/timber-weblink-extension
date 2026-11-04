<?php

/**
 * A classic template: output starts before wp_footer, and a Twig template is rendered after it.
 */

declare(strict_types=1);

use Timber\Timber;

?><!DOCTYPE html>
<html>
<head><?php \wp_head(); ?></head>
<body>
<?php Timber::render_string("<link rel=\"stylesheet\" href=\"{{ preload('/app.css', {as: 'style'}) }}\">"); ?>
<?php \wp_footer(); ?>
<?php Timber::render_string("<script src=\"{{ preload('/late.js', {as: 'script'}) }}\"></script>"); ?>
</body>
</html>
