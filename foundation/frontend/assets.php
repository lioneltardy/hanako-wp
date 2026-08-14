<?php

use HanakoWP\Tools\AssetRegistry;
use HanakoWP\Tools\ThemeOptions;
use HanakoWP\Tools\ViteManifest;

if (! defined('ABSPATH')) exit;

/** Get the URI of an asset in the dist/assets directory. */
function hw_asset(string $file) {
  return get_bloginfo('template_directory') . '/dist/assets/' . $file;
}

/** Enqueue a theme asset from the dist/assets directory. */
$options = ThemeOptions::instance();
$dev_suffix = $options->get('dev_mode') ? '?time=' . date('U') : '';
$manifest = ViteManifest::instance();
$asset_registry = AssetRegistry::instance();

foreach ($manifest->css_uris('views/css/style.css') as $index => $style_uri) {
  $asset_registry->add_style('hw-style-' . $index, $style_uri . $dev_suffix);
}

$hw_legacy_style_uri = '';
$legacy_style_path = get_template_directory() . '/dist/css/style-legacy.css';
if (file_exists($legacy_style_path)) {
  $hw_legacy_style_uri = get_bloginfo('template_directory') . '/dist/css/style-legacy.css' . $dev_suffix;
}

$vite_script = $manifest->asset_uri('views/ts/script.ts');
if (!empty($vite_script)) {
  $asset_registry->add_script('hw-script', $vite_script . $dev_suffix);
}

$external_index = 0;
foreach (explode("\n", (string) $options->get('externals_scripts')) as $external_script) {
  $external_script = trim($external_script);
  if ($external_script === '') continue;

  $external_script = str_replace('{template_directory}', get_bloginfo('template_directory'), $external_script);
  $external_script = str_replace('{dev}', $dev_suffix, $external_script);
  $asset_registry->add_script('external_' . $external_index, $external_script);

  $external_index++;
}

foreach (explode("\n", (string) $options->get('externals_css')) as $external_css) {
  $external_css = trim($external_css);
  if ($external_css === '') continue;

  $external_css = str_replace('{template_directory}', get_bloginfo('template_directory'), $external_css);
  $external_css = str_replace('{dev}', $dev_suffix, $external_css);
  $asset_registry->add_style('external_' . $external_index, $external_css);

  $external_index++;
}

add_action('wp_head', function () use ($hw_legacy_style_uri) {
  if (!empty($hw_legacy_style_uri)) {
    ?>
    <link id="hw-style-legacy-css" rel="stylesheet" data-href="<?php echo esc_url($hw_legacy_style_uri); ?>">
    <?php
  }

  ?>
  <script>
    (function () {
      var supportsLayers = typeof window.CSSLayerBlockRule !== 'undefined';
      var supportsNesting = typeof window.CSSNestedDeclarations !== 'undefined';

      if (supportsLayers && supportsNesting) return;

      var legacyStylesheet = document.getElementById('hw-style-legacy-css');
      if (!legacyStylesheet) return;

      var legacyHref = legacyStylesheet.getAttribute('data-href');
      if (legacyHref) legacyStylesheet.setAttribute('href', legacyHref);
    })();
  </script>
  <?php
}, 1);

if (!is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php') {
  add_filter('style_loader_tag', function ($tag) {
    return str_replace(' href', ' defer href', $tag);
  });

  add_filter('script_loader_tag', function ($tag, $handle) {
    if ($handle === 'hw-script' && strpos($tag, 'type=') === false) {
      $tag = str_replace('<script ', '<script type="module" ', $tag);
    }

    if (strpos($tag, 'wp-includes') !== false || strpos($tag, 'wp-content/plugins') !== false) return $tag;

    return str_replace(' src', ' defer src', $tag);
  }, 10, 2);
}

add_action('wp_enqueue_scripts', function () {
  wp_dequeue_style('classic-theme-styles');
}, 20);

add_action('wp_enqueue_scripts', function () {
  wp_dequeue_style('global-styles');
}, 100);
