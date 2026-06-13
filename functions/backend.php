<?php
/*
 * Allow HTML in ACF fields
 *
 * More informations: https://www.advancedcustomfields.com/resources/html-escaping/
 */
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

/*
 * Add custom image sizes
 */
add_action('after_setup_theme', function () {
  add_image_size('medium-2x', 2000, 0, false);
  add_image_size('big', 5000, 0, false);
});
