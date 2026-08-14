<?php

use HanakoWP\Tools\ThemeOptions;
use HanakoWP\Tools\ViteManifest;

if (! defined('ABSPATH')) exit;

/** Administration interface customization. */
if (!defined('WP_AUTO_UPDATE_CORE')) {
  add_action('admin_notices', function () {
    echo '<div class="error notice"><p>' . __('Please add the following line to your wp-config.php file.<pre>define(\'WP_AUTO_UPDATE_CORE\', \'minor\');</pre>', 'abb') . '</p></div>';
  });
}

add_filter('wpseo_metabox_prio', function () {
  return 'low';
});

add_action('login_head', function () {
  echo '<style type="text/css">.login h1 a {background-image:url(' . get_bloginfo('template_directory') . '/logo-dashboard.png);background-size:218px;width:218px;height:40px;}</style>';
});

$editor_style_path = ViteManifest::instance()->asset_path('views/css/editor-style.css');
if (!empty($editor_style_path)) {
  add_editor_style(ltrim($editor_style_path, '/'));
}

add_action('wp_dashboard_setup', function () {
  $logo_url = esc_url(get_bloginfo('template_directory') . '/logo-dashboard.png');
  $site_name = esc_html(get_bloginfo('name'));
  $widget_title = '<div style="text-align:center;"><img style="vertical-align:middle;height:23px;width:190px;" src="' . $logo_url . '" alt=""></div>';

  wp_add_dashboard_widget('aboutblank_dashboard_widget', $widget_title, function () use ($site_name) {
    echo 'Bienvenue sur la plateforme d\'administration du site:<br><strong>' . $site_name . '</strong><br><br>About Blank Design Office<br>Ch. des Pépinères 20<br>CH-1020 Renens<br><br><a href="mailto:lionel@aboutblank.ch">lionel@aboutblank.ch</a><br>+41 21 635 03 22<br>+41 78 718 74 45';
  });

  remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
});

remove_action('welcome_panel', 'wp_welcome_panel');

add_action('admin_init', function () {
  add_filter('admin_footer_text', '__return_false', 11);
  add_filter('update_footer', function () {
    return '© ' . date('Y') . ' <a target="_blank" href="https://www.aboutblank.ch">About Blank Design Office</a>';
  }, 11);

  if (!current_user_can('update_core')) {
    remove_submenu_page('index.php', 'update-core.php');
  }
});

add_action('admin_head', function () {
  $screen = get_current_screen();

  if ($screen) remove_meta_box('icl_div_config', $screen->post_type, 'normal');
}, 1);

add_action('admin_head', function () {
  remove_action('admin_notices', 'update_nag', 3);
}, 1);

add_action('admin_head', function () {
  $selectors = array_filter(array_map(function ($selector) {
    return trim(wp_strip_all_tags($selector));
  }, explode(PHP_EOL, (string) ThemeOptions::instance()->get('hide_css'))));

  if (empty($selectors)) return;

  echo '<style>' . implode(',', $selectors) . '{display:none!important;}</style>';
});

add_action('wp_before_admin_bar_render', function () {
  global $wp_admin_bar;

  $wp_admin_bar->remove_menu('wp-logo');
  $wp_admin_bar->remove_menu('comments');
  $wp_admin_bar->remove_menu('itsec_admin_bar_menu');
  $wp_admin_bar->remove_menu('wpseo-menu');

  if (!current_user_can('update_core')) $wp_admin_bar->remove_menu('updates');
});

add_filter('manage_pages_columns', function ($columns) {
  unset($columns['comments']);

  return $columns;
});

add_theme_support('menus');

if (ThemeOptions::instance()->get('hide_screen_options')) {
  add_filter('screen_options_show_screen', function () {
    return false;
  });
}

if (ThemeOptions::instance()->get('hide_help')) {
  add_filter('contextual_help', function ($old_help, $screen_id, $screen) {
    $screen->remove_help_tabs();

    return $old_help;
  }, 999, 3);
}

add_action('admin_menu', function () {
  $metaboxes = explode(PHP_EOL, (string) ThemeOptions::instance()->get('hide_metabox'));

  foreach ($metaboxes as $metabox) {
    [$id, $screen, $context] = array_pad(array_map('trim', explode(',', $metabox)), 3, '');
    if ($id !== '' && $screen !== '' && $context !== '') {
      remove_meta_box($id, $screen, $context);
    }
  }

  $metaboxes_posttype = explode(PHP_EOL, (string) ThemeOptions::instance()->get('hide_metabox_posttype'));
  foreach (get_post_types() as $post_type) {
    foreach ($metaboxes_posttype as $metabox) {
      [$id, $context] = array_pad(array_map('trim', explode(',', $metabox)), 2, '');
      if ($id !== '' && $context !== '') {
        remove_meta_box($id, $post_type, $context);
      }
    }
  }
});

add_filter('site_status_tests', function ($tests) {
  unset($tests['direct']['wordpress_version']);
  unset($tests['direct']['plugin_version']);
  unset($tests['direct']['theme_version']);
  unset($tests['direct']['background_updates']);
  unset($tests['async']['background_updates']);

  return $tests;
});

if (!ThemeOptions::instance()->get('show_admin_bar')) add_filter('show_admin_bar', '__return_false');
