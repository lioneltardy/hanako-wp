<?php

use HanakoWP\Tools\ThemeOptions;

if (! defined('ABSPATH')) exit;

/** Register custom ACF WYSIWYG toolbars */
add_filter('acf/fields/wysiwyg/toolbars', function($toolbars) {
  $toolbars_data = json_decode(ThemeOptions::instance()->get('toolbars'));

  if (is_array($toolbars_data)) {
    foreach ($toolbars_data as $toolbar) {
      $toolbars[$toolbar->title] = [];
      $toolbars[$toolbar->title][1] = $toolbar->data;
    }
  }

	unset($toolbars['Basic']);

	return $toolbars;
});

/** Set TinyMCE options */
add_filter('tiny_mce_before_init', function($init_array) {
  $options = ThemeOptions::instance();

  if ($options->get('paste_as_text')) $init_array['paste_as_text'] = 'true';
  if ($options->get('style_formats')) $init_array['style_formats'] = $options->get('style_formats');
  if ($options->get('block_formats')) $init_array['block_formats'] = $options->get('block_formats');

  return $init_array;
});
