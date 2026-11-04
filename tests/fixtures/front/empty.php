<?php

/**
 * A template without links.
 */

declare(strict_types=1);

use Timber\Timber;

?><!DOCTYPE html>
<html>
<head><?php \wp_head(); ?></head>
<body>
<?php Timber::render_string('<p>{{ "No links" }}</p>'); ?>
<?php \wp_footer(); ?>
</body>
</html>
