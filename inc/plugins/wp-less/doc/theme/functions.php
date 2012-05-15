<?php

if (!is_admin())
{
  wp_enqueue_style('wp-less-sample-theme', get_template_directory_uri().'/screen.less', array(), '1.0', 'screen,projection');
}
