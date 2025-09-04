<?php

use Timber\Timber;
use Timber\ImageHelper;

/*
 * Timber
 */

global $installNotices;
$installNotices = [];
if (!class_exists('Timber')) {
  $installNotices[] = [
    'url' => esc_url(admin_url('/plugin-install.php?s=Timber&tab=search&type=term')),
    'title' => 'Timber'
  ];

  add_filter('template_include', function ($template) {
    return get_stylesheet_directory() . '/no-timber.html';
  });
} else {
  Timber::$dirname = ['views/twig'];
}

/*
 * Dev mode
 */
if (get_abb_option('dev_mode')) {
  add_filter('body_class', function ($classes) {
    $classes[] =  'dev-mode';

    return $classes;
  });
}

/*
 * Include
 */
$dev_suffix = (get_abb_option('dev_mode') ? '?time=' . date('U') : '');
$abb_styles[] = ['hw-style', get_bloginfo('template_directory') . '/dist/css/style-v' . ASSETS_VERSION . '.css' . $dev_suffix, false];
$abb_scripts[] = ['hw-script-runtime', get_bloginfo('template_directory') . '/dist/js/runtime-v' . ASSETS_VERSION . '.js' . $dev_suffix];
$abb_scripts[] = ['hw-script-vendor', get_bloginfo('template_directory') . '/dist/js/vendors-v' . ASSETS_VERSION . '.js' . $dev_suffix];
$abb_scripts[] = ['hw-script-bootstrap', get_bloginfo('template_directory') . '/dist/js/bootstrap-v' . ASSETS_VERSION . '.js' . $dev_suffix];
$abb_scripts[] = ['hw-script-hanako', get_bloginfo('template_directory') . '/dist/js/hanako-v' . ASSETS_VERSION . '.js' . $dev_suffix];
$abb_scripts[] = ['hw-script', get_bloginfo('template_directory') . '/dist/js/script-v' . ASSETS_VERSION . '.js' . $dev_suffix];

$i = 0;
$externals_scripts = explode("\n", get_abb_option('externals_scripts'));
if (is_array($externals_scripts)) {
  foreach ($externals_scripts as $external_script) {
    $external_script = str_replace('{template_directory}', get_bloginfo('template_directory'), $external_script);
    $external_script = str_replace('{dev}', $dev_suffix, $external_script);
    $abb_scripts[] = ['external_' . $i, $external_script];

    $i++;
  }
}

$externals_css = explode("\n", get_abb_option('externals_css'));
if (is_array($externals_css)) {
  foreach ($externals_css as $external_css) {
    $external_css = str_replace('{template_directory}', get_bloginfo('template_directory'), $external_css);
    $external_css = str_replace('{dev}', $dev_suffix, $external_css);
    $abb_styles[] = ['external_' . $i, $external_css];

    $i++;
  }
}

/*
 * Disable the Admin Bar
 */
if (!get_abb_option('show_admin_bar')) add_filter('show_admin_bar', '__return_false');

/*
 * Redirect
 */
add_action('init', function () {
  if (!is_user_logged_in() && get_abb_option('hide_site') && $GLOBALS['pagenow'] !== 'wp-login.php' && !wp_doing_ajax() && strpos($_SERVER['REQUEST_URI'], 'wp-json') === false) {
    $allowed_urls = explode(',', get_abb_option('allowed_urls'));

    if (!in_array($_SERVER['REQUEST_URI'], $allowed_urls) && $_SERVER['REQUEST_URI'] != get_abb_option('redirect_to') && !isset($_GET['wc-api'])) {
      wp_redirect(get_abb_option('redirect_to'));
      exit;
    }
  }
});

/* Retrive asset */
function hw_asset($file) {
  return get_bloginfo('template_directory') . '/dist/assets/' . $file;
}

/*
 * Image with lazy loading
 */
/*
 * Image with lazy loading
 */
function hw_lazy_image($image, $size, $classes = '', $alt = '', $title = '', $data = '') {
  $timber_image = Timber::get_image($image);

  if (empty($timber_image)) return;

  $ratio = $timber_image->aspect() > 0 ? 100 / $timber_image->aspect : 1;

  if (isset($timber_image->sizes[$size])) $ratio = $timber_image->sizes[$size]['height'] != 0 ? 100 / ($timber_image->sizes[$size]['width'] / $timber_image->sizes[$size]['height']) : 0;

  $alt = (!empty($alt)) ? $alt : $timber_image->alt;
  $title = (!empty($title)) ? $alt : $timber_image->title;

  $srcs = [];
  foreach (['', '-2x'] as $suffix) {
    $src = $timber_image->src($size . $suffix);
    $src = ENABLE_WEBP ? ImageHelper::img_to_webp($src) : $src;

    $srcs[] = $src;
  }

  $return  = '<div class="ratio ' . $classes . '" style="--bs-aspect-ratio: ' . $ratio . '%;">';
  $return .= '<img data-hw-src="' . implode(';', $srcs) . '" class="d-block w-100" title="' . $title . '" alt="' . $alt . '" ' . $data . '>';
  $return .= '</div>';

  return $return;
}

/*
 * Image background with lazy loading
 */
function hw_lazy_background_image($image, $size) {
  $timber_image = Timber::get_image($image);

  if (empty($timber_image)) return;

  $srcs = [];
  foreach (['', '-2x'] as $suffix) {
    $src = $timber_image->src($size . $suffix);
    $src = ENABLE_WEBP ? ImageHelper::img_to_webp($src) : $src;

    $srcs[] = $src;
  }

  return 'data-hw-background-image="' . implode(';', $srcs) . '"';
}

/*
 * add some useful functions
 */
add_filter('timber/twig/functions', function ($functions) {
  $functions['get_permalink'] = [
    'callable' => 'get_the_permalink',
  ];

  $functions['get_field'] = [
    'callable' => 'get_field',
  ];

  $functions['print_r'] = [
    'callable' => 'print_r',
  ];

  $functions['asset'] = [
    'callable' => 'hw_asset',
  ];

  $functions['lazy_image'] = [
    'callable' => 'hw_lazy_image',
  ];

  $functions['lazy_background_image'] = [
    'callable' => 'hw_lazy_background_image',
  ];

  return $functions;
});

/*
 * Fetch templates from models/subfolder
 */
$templateTypes = ['index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'embed', 'home', 'frontpage', 'privacypolicy', 'page', 'paged', 'search', 'single', 'singular', 'attachment'];

foreach ($templateTypes as $templateType) {
  add_filter($templateType . '_template_hierarchy', function ($templates) {
    foreach ($templates as &$template) {
      if (strpos($template, 'odels/') != 1) {
        $template = 'models/' . $template;
      }
    }

    return $templates;
  });
}

/*
 * Load translations
 */
add_action('after_setup_theme', function () {
  load_theme_textdomain('hw-theme', get_template_directory() . '/languages');
});

/*
 * Add defered attribute to style and script tag
 */
if (!is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php') {
  add_filter('style_loader_tag', function ($tag) {
    return str_replace(' href', ' defer href', $tag);

    return $tag;
  });

  add_filter('script_loader_tag', function ($tag) {
    return str_replace(' src', ' defer src', $tag);

    return $tag;
  });
}

/*
 * Remove default theme stylesheet
 */
add_action('wp_enqueue_scripts', function () {
  wp_dequeue_style('classic-theme-styles');
}, 20);

/*
 * Remove usless meta & other stuff
 */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'rsd_link');

/*
 * Disable inline styles and other WordPress stuff
 */
add_action('wp_enqueue_scripts', function () {
  wp_dequeue_style('global-styles');
}, 100);

add_filter('wp_img_tag_add_auto_sizes', '__return_false');

remove_filter('wp_robots', 'wp_robots_max_image_preview_large');
