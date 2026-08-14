<?php

if (! defined('ABSPATH')) exit;

/** Disable Gutenberg Editor */
add_filter('use_block_editor_for_post', '__return_false');

/** Set default image link type to "file" */
update_option('image_default_link_type', 'file');

/** Force rewrite permalinks to use post name */
add_action('after_switch_theme', function () {
  global $wp_rewrite;

  $wp_rewrite->set_permalink_structure('/%postname%/');
  $wp_rewrite->flush_rules(true);
});

/** Disable WP Rocket Lazy Load */
add_filter('rocket_lrc_optimization', '__return_false', 999);
