<?php
require_once(__DIR__ . '/../../vendor/autoload.php');

Timber\Timber::init();

$abb_scripts = [];
$abb_styles = [];
$abb_options = [];

function get_abb_option($key) {
  global $abb_options;

  if (empty($abb_options)) {
    $frontend = get_option('abb_options_frontend');
    if (!is_array($frontend)) $frontend = [];

    $menu = get_option('abb_options_menu');
    if (!is_array($menu)) $menu = [];

    $backend = get_option('abb_options_backend');
    if (!is_array($backend)) $backend = [];

    $cpt = get_option('abb_options_cpt');
    if (!is_array($cpt)) $cpt = [];

    $tinymce = get_option('abb_options_tinymce');
    if (!is_array($tinymce)) $tinymce = [];

    $vendor = get_option('abb_options_vendor');
    if (!is_array($vendor)) $vendor = [];

    $abb_options = array_merge($frontend, $menu, $backend, $cpt, $tinymce, $vendor);
  }

  return (isset($abb_options[$key])) ? $abb_options[$key] : false;
}

include_once('panel.php');
include_once('frontend.php');
include_once('backend.php');
include_once('tinymce.php');

define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');

add_action('wp_enqueue_scripts', function () {
  global $abb_scripts, $abb_styles;

  wp_dequeue_style('wp-block-library');

  foreach ($abb_styles as $styles) {
    wp_register_style($styles[0], $styles[1], false, 1, (!empty($styles[2])) ? $styles[2] : 'all');
    wp_enqueue_style($styles[0]);
  }

  foreach ($abb_scripts as $script) {
    $handle = (isset($script[0])) ? $script[0] : 'handle_' . time() . '_' . rand(0, 1024);
    $src = (isset($script[1])) ? $script[1] : '';
    $in_footer = (isset($script[2])) ? $script[2] : false;

    if (!empty($src)) {
      wp_register_script($handle, $src, [], false, $in_footer);
      wp_enqueue_script($handle);
    }

    if ($handle == 'hw-scripts') {
      wp_localize_script($handle, 'HWP', [
        'template_url' => get_stylesheet_directory_uri(),
        'ajax_url' => admin_url('admin-ajax.php')
      ]);
    }
  }
});

/* remove usless stuff */
add_action('wp_footer', function () {
  wp_dequeue_script('wp-embed');
});

/*
 * Install noticies
 */
add_action('admin_notices', function () {
  global $installNotices;

  if (empty($installNotices)) return;

  echo '<div class="notice notice-warning"><p>Warning some mandatory plugins are missing.</p><ul>';

  foreach ($installNotices as $installNotice) {
    $url = (isset($installNotice['url'])) ? $installNotice['url'] : $installNotice['external_url'];
    $target = (!empty($installNotice['external_url'])) ? 'target="_blank"' : '';
    echo '<li><a href="' . $url . '" ' . $target . '>' . $installNotice['title'] . '</a></li>';
  }

  echo '</ul></div>';
});

/*
 * Fix OB Flush bug
 */
remove_action('shutdown', 'wp_ob_end_flush_all', 1);
add_action('shutdown', function () {
  while (@ob_end_flush());
});

/*
 * Error reporting
 */
if (get_abb_option('error_reporting')) {
  $error_reporting = get_abb_option('error_reporting');

  ini_set('display_errors', 1);

  if ($error_reporting == 1) error_reporting(E_ALL);
  if ($error_reporting == 2) error_reporting(E_ERROR);
  if ($error_reporting == 3) error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
  if ($error_reporting == 4) error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED);
  if ($error_reporting == 5) error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
} else {
  ini_set('display_errors', 0);
}
