<?php

use HanakoWP\Tools\ThemeOptions;

if (! defined('ABSPATH')) exit;

/** Redirect non-logged-in users to a specific page if the site is hidden */
add_action('init', function () {
  if (is_user_logged_in() || !ThemeOptions::instance()->get('hide_site') || $GLOBALS['pagenow'] === 'wp-login.php' || wp_doing_ajax() || isset($_GET['wc-api'])) return;

  $normalize_path = static function ($url) {
    $path = wp_parse_url((string) $url, PHP_URL_PATH);
    $path = $path === null || $path === false ? '/' : $path;
    $path = '/' . ltrim($path, '/');
    $path = untrailingslashit($path);

    return $path === '' ? '/' : $path;
  };

  $request_path = $normalize_path(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'));
  $rest_path = '/' . trim(rest_get_url_prefix(), '/');
  if ($request_path === $rest_path || str_starts_with($request_path, $rest_path . '/')) return;

  $redirect_to = (string) ThemeOptions::instance()->get('redirect_to');
  $redirect_path = $normalize_path($redirect_to);
  $allowed_urls = preg_split('/[\r\n,]+/', (string) ThemeOptions::instance()->get('allowed_urls'));
  $allowed_paths = array_filter(array_map(function ($url) use ($normalize_path) {
    $url = trim($url);

    return $url === '' ? null : $normalize_path($url);
  }, $allowed_urls));

  if (!in_array($request_path, $allowed_paths, true) && $request_path !== $redirect_path) {
    wp_safe_redirect($redirect_to ?: home_url('/'));
    exit;
  }
});
