<?php

if (! defined('ABSPATH')) exit;

/** Allow HTML in ACF fields */
add_filter('wp_kses_allowed_html', function ($tags, $context) {
  if ($context === 'acf') {
    $tags['iframe'] = [
      'src'             => true,
      'height'          => true,
      'width'           => true,
      'frameborder'     => true,
      'allowfullscreen' => true,
      'title'           => true,
      'allow'           => true
    ];
  }
  return $tags;
}, 10, 2);

/** Add custom image sizes */
add_action('after_setup_theme', function () {
  //add_image_size('medium-2x', 1200, 0, false);
  //add_image_size('large-2x', 4000, 0, false);
});

/** Enable thumbnails support */
//add_theme_support('post-thumbnails');
