<?php

if (! defined('ABSPATH')) exit;

/** Populate Timber Context */
add_filter('timber/context', function ($context) {
  $context['menus'] = [
    'main' => Timber::get_menu('main')
  ];

  return $context;
});
