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

function hw_get_vite_manifest() {
  static $manifest = null;

  if ($manifest !== null) return $manifest;

  $manifest_path = get_template_directory() . '/dist/.vite/manifest.json';

  if (!file_exists($manifest_path)) {
    $manifest = [];
    return $manifest;
  }

  $raw_manifest = file_get_contents($manifest_path);
  $decoded_manifest = json_decode((string)$raw_manifest, true);

  $manifest = is_array($decoded_manifest) ? $decoded_manifest : [];

  return $manifest;
}

function hw_get_vite_asset_path($entry_name) {
  $manifest = hw_get_vite_manifest();

  if (!isset($manifest[$entry_name]['file'])) return '';

  return '/dist/' . ltrim($manifest[$entry_name]['file'], '/');
}

function hw_get_vite_asset_uri($entry_name) {
  $asset_path = hw_get_vite_asset_path($entry_name);

  if (empty($asset_path)) return '';

  return get_bloginfo('template_directory') . $asset_path;
}

function hw_get_vite_css_uris($entry_name) {
  $manifest = hw_get_vite_manifest();

  if (!isset($manifest[$entry_name])) return [];

  $css_files = [];

  if (!empty($manifest[$entry_name]['file']) && str_ends_with($manifest[$entry_name]['file'], '.css')) {
    $css_files[] = $manifest[$entry_name]['file'];
  }

  if (!empty($manifest[$entry_name]['css']) && is_array($manifest[$entry_name]['css'])) {
    $css_files = array_merge($css_files, $manifest[$entry_name]['css']);
  }

  $css_files = array_values(array_unique($css_files));

  return array_map(function ($file) {
    return get_bloginfo('template_directory') . '/dist/' . ltrim($file, '/');
  }, $css_files);
}

$vite_styles = hw_get_vite_css_uris('views/css/style.css');
foreach ($vite_styles as $index => $style_uri) {
  $abb_styles[] = ['hw-style-' . $index, $style_uri . $dev_suffix, false];
}

$vite_script = hw_get_vite_asset_uri('views/ts/script.ts');
if (!empty($vite_script)) {
  $abb_scripts[] = ['hw-script', $vite_script . $dev_suffix];
}

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
 * Convert image source to WebP and optionally remove the source JPG/PNG when
 * it is an intermediate generated size (e.g. image-1200x800.jpg).
 */
function hw_to_webp($src, $delete_source = true) {
  global $wpdb;

  if (!defined('ENABLE_WEBP') || !ENABLE_WEBP || empty($src) || !class_exists('Timber\\ImageHelper')) {
    return $src;
  }

  $webp_src = ImageHelper::img_to_webp($src);
  if (empty($webp_src)) return $src;

  if ($delete_source) {
    $src_without_query  = strtok((string)$src, '?');
    $webp_without_query = strtok((string)$webp_src, '?');

    $upload = wp_upload_dir();
    $baseurl = isset($upload['baseurl']) ? $upload['baseurl'] : '';
    $basedir = isset($upload['basedir']) ? $upload['basedir'] : '';

    if (!empty($baseurl) && !empty($basedir) && strpos($src_without_query, $baseurl) === 0) {
      $relative_src_path  = ltrim(substr($src_without_query, strlen($baseurl)), '/');
      $relative_webp_path = ltrim(substr($webp_without_query, strlen($baseurl)), '/');

      $source_path = rtrim($basedir, '/') . '/' . $relative_src_path;
      $webp_path   = rtrim($basedir, '/') . '/' . $relative_webp_path;

      $is_intermediate_size = (bool)preg_match('/-\\d+x\\d+\.(jpe?g|png)$/i', basename($source_path));

      // Update media size metadata to point to the WebP version
      if ($is_intermediate_size) {
        $filename = basename($source_path);

        $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attachment_metadata' AND meta_value = LIKE %" . $filename . "%"));

        if ($attachment_id) {
          echo 'Found attachment ID ' . $attachment_id . ' for source image ' . $source_path . "\n";
          $metadata = wp_get_attachment_metadata($attachment_id);
          if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as &$size) {
              if (isset($size['file']) && $size['file'] === basename($source_path)) {
                $size['file'] = basename($webp_path);
                break;
              }
            }
            echo 'Updating attachment metadata for ' . $source_path . ' to point to ' . $webp_path . "\n";
            wp_update_attachment_metadata($attachment_id, $metadata);
          }
        }
      }

      if ($is_intermediate_size && file_exists($source_path) && file_exists($webp_path)) {
        @unlink($source_path);
      }
    }
  }

  return $webp_src;
}

/*
 * Image with lazy loading
 */
function hw_get_srcs($timber_image, $size) {
  $srcs = [];

  foreach (['', '-2x'] as $suffix) {
    $timber_image->sizes;
    if ($suffix === '-2x' && !isset($timber_image->sizes[$size . $suffix])) continue;

    $src = $timber_image->src($size . $suffix);
    $src = ENABLE_WEBP ? hw_to_webp($src) : $src;

    $srcs[] = $src;
  }

  return $srcs;
}

function hw_lazy_image($image, $size, $classes = '', $alt = '', $title = '', $data = '') {
  $timber_image = Timber::get_image($image);

  if (empty($timber_image)) return;

  $ratio = $timber_image->aspect() > 0 ? 100 / $timber_image->aspect : 1;

  if (isset($timber_image->sizes[$size])) $ratio = $timber_image->sizes[$size]['height'] != 0 ? 100 / ($timber_image->sizes[$size]['width'] / $timber_image->sizes[$size]['height']) : 0;

  $alt = (!empty($alt)) ? $alt : $timber_image->alt;
  $title = (!empty($title)) ? $alt : $timber_image->title;

  $srcs = hw_get_srcs($timber_image, $size);

  $return  = '<div class="hw-ratio ' . $classes . '" style="--hw-aspect-ratio: ' . $ratio . '%;">';
  $return .= '<img data-hw-src="' . implode(';', $srcs) . '" class="hw-img-fluid" title="' . $title . '" alt="' . $alt . '" ' . $data . '>';
  $return .= '</div>';

  return $return;
}

/*
 * Image background with lazy loading
 */
function hw_lazy_background_image($image, $size) {
  $timber_image = Timber::get_image($image);

  if (empty($timber_image)) return;

  $srcs = hw_get_srcs($timber_image, $size);

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

add_filter('timber/twig', function ($twig) {
  $twig->addFilter(new \Twig\TwigFilter('force_webp', function ($src) {
    return hw_to_webp($src);
  }));

  return $twig;
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
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

/*
 * Disable inline styles and other WordPress stuff
 */
add_action('wp_enqueue_scripts', function () {
  wp_dequeue_style('global-styles');
}, 100);

add_filter('wp_img_tag_add_auto_sizes', '__return_false');

remove_filter('wp_robots', 'wp_robots_max_image_preview_large');
