<?php

use Timber\Timber;
use HanakoWP\Tools\ThemeOptions;

if (! defined('ABSPATH')) exit;

/** Timber initialization */
Timber::init();
Timber::$dirname = ['views/twig'];

if (ThemeOptions::instance()->get('dev_mode')) {
  add_filter('body_class', function ($classes) {
    $classes[] = 'dev-mode';

    return $classes;
  });
}

/** Add custom Timber functions */
add_filter('timber/twig/functions', function ($functions) {
  $functions['get_permalink'] = ['callable' => 'get_the_permalink'];
  $functions['get_field'] = ['callable' => 'get_field'];
  $functions['asset'] = ['callable' => 'hw_asset'];
  $functions['lazy_image'] = ['callable' => 'hw_lazy_image'];
  $functions['lazy_background_image'] = ['callable' => 'hw_lazy_background_image'];
  $functions['print_r'] = ['callable' => 'print_r'];

  return $functions;
});

/** Add custom Twig filters */
add_filter('timber/twig', function ($twig) {
  $twig->addFilter(new \Twig\TwigFilter('force_webp', function ($src) {
    return hw_to_webp($src);
  }));

  return $twig;
});

/** Populate Timber Context */
add_filter('timber/context', function ($context) {
  $context['is_dev_mode'] = ThemeOptions::instance()->get('dev_mode');

  return $context;
});
