<?php

use HanakoWP\Tools\ThemeOptions;
use HanakoWP\Tools\AssetRegistry;

if (! defined('ABSPATH')) exit;

/** Load Foundation Functions */
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/tools/AssetRegistry.php');
require_once(__DIR__ . '/tools/ThemeOptions.php');
require_once(__DIR__ . '/tools/ViteManifest.php');

include_once(__DIR__ . '/admin/config.php');
include_once(__DIR__ . '/admin/editor.php');
include_once(__DIR__ . '/admin/interface.php');
include_once(__DIR__ . '/admin/media.php');
include_once(__DIR__ . '/admin/permissions.php');
include_once(__DIR__ . '/admin/settings.php');

if (ThemeOptions::instance()->get('disable_comments')) include_once(__DIR__ . '/features/comments.php');
include_once(__DIR__ . '/features/fixes.php');

include_once(__DIR__ . '/frontend/access.php');
include_once(__DIR__ . '/frontend/assets.php');
include_once(__DIR__ . '/frontend/images.php');
include_once(__DIR__ . '/frontend/timber.php');
include_once(__DIR__ . '/frontend/templates.php');

include_once(__DIR__ . '/security/rest-api.php');
include_once(__DIR__ . '/security/uploads.php');

/** Enqueue theme scripts and styles */
add_action('wp_enqueue_scripts', [AssetRegistry::instance(), 'enqueue']);

/** Set error reporting */
if (ThemeOptions::instance()->get('error_reporting')) {
  $error_reporting = ThemeOptions::instance()->get('error_reporting');

  ini_set('display_errors', 1);

  if ($error_reporting == 1) error_reporting(E_ALL);
  if ($error_reporting == 2) error_reporting(E_ERROR);
  if ($error_reporting == 3) error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
  if ($error_reporting == 4) error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED);
  if ($error_reporting == 5) error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
} else {
  ini_set('display_errors', 0);
}
