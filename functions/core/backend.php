<?php
/*
 * Disables WordPress from generating thumbnail images when a PDF is uploaded
 * Code credit to http://www.wpbeginner.com/wp-tutorials/how-to-disable-pdf-thumbnail-previews-in-wordpress/
 */
add_filter('fallback_intermediate_image_sizes', function () {
  return [];
});

/*
 * Disable Gutenberg
 */
add_filter('use_block_editor_for_post', '__return_false');

/*
 * Notice the remind wp-config edition is needed
 */
if (!defined('WP_AUTO_UPDATE_CORE')) {
  add_action('admin_notices', function () {
    echo '<div class="error notice"><p>' . __('Please add the following line to your wp-config.php file.<pre>define(\'WP_AUTO_UPDATE_CORE\', \'minor\');</pre>', 'abb') . '</p></div>';
  });
}

/*
 * Image quality modification
 */
add_filter('jpeg_quality', function () {
  return 100;
});
add_filter('big_image_size_threshold', '__return_false');

/*
 * Remove 768, 1536 and medium_large image sizes
 */
add_filter('intermediate_image_sizes_advanced', function ($sizes) {
  unset($sizes['1536x1536']);
  unset($sizes['2048x2048']);

  return $sizes;
});

add_action('init', function () {
  remove_image_size('1536x1536');
  remove_image_size('2048x2048');
});

add_filter('intermediate_image_sizes', function ($sizes) {
  return array_filter($sizes, function ($val) {
    return 'medium_large' !== $val;
  });
});

/*
 * Allow svg upload
 */
define('ALLOW_UNFILTERED_UPLOADS', true);

add_filter('upload_mimes', function ($mimes) {
  $mimes['svg'] = 'image/svg+xml';

  return $mimes;
});

// Add unfiltered_upload capability to editor role
add_action('admin_init', function () {
  $role = get_role('editor');

  if ($role) {
    $role->add_cap('unfiltered_upload');
  }
});

/*
 * Move Yoast to bottom
 */
add_filter('wpseo_metabox_prio', function () {
  return 'low';
});

/*
 * Logo login
 */
add_action('login_head', function () {
  echo '<style type="text/css">.login h1 a {background-image:url(' . get_bloginfo('template_directory') . '/logo-dashboard.png);background-size:218px;width: 218px;height: 40px;}</style>';
});

/*
 * Enable editor style menus
 */
$editor_style_path = hw_get_vite_asset_path('views/css/editor-style.css');
if (!empty($editor_style_path)) {
  add_editor_style(ltrim($editor_style_path, '/'));
}

/*
 * Message on the dashboard
 */
add_action('wp_dashboard_setup', function () {
  wp_add_dashboard_widget('aboutblank_dashboard_widget', '<div style="text-align:center;"><img style="vertical-align: middle; height: 23px;width:190px;" src="' . get_bloginfo('template_directory') . '/logo-dashboard.png" alt=""></div>', function () {
    echo 'Bienvenue sur la plateforme d\'administration du site:<br><strong>' . get_bloginfo('name') . '</strong><br/><br/>About Blank Design Office<br>Ch. des Pépinères 20<br>CH-1020 Renens<br>&nbsp;<br><a href="mailto:lionel@aboutblank.ch">lionel@aboutblank.ch</a><br>+41 21 635 03 22<br>+41 78 718 74 45';
  });
});

remove_action('welcome_panel', 'wp_welcome_panel');

/*
 * Change/Disable the footer text line
 */
add_action('admin_init', function () {
  add_filter('admin_footer_text', '__return_false', 11);
  add_filter('update_footer', function ($content) {
    return '© ' . date('Y') . ' <a target="_blank" href="http://www.aboutblank.ch">About Blank Design Office</a>';
  }, 11);
});

/*
 * Remove WPML metabox
 */
add_action('admin_head', function () {
  $screen = get_current_screen();

  remove_meta_box('icl_div_config', $screen->post_type, 'normal');
});

/*
 * Image linked to files
 */
update_option('image_default_link_type', 'file');

/*
 * Remove french accent on image upload
 */
add_filter('sanitize_file_name', 'remove_accents');

/*
 * Hide update notification
 */
add_action('admin_head', function () {
  remove_action('admin_notices', 'update_nag', 3);
}, 1);

/*
 * Remove links in admin bar
 */
add_action('wp_before_admin_bar_render', function () {
  global $wp_admin_bar;

  $wp_admin_bar->remove_menu('wp-logo');
  $wp_admin_bar->remove_menu('comments');
  $wp_admin_bar->remove_menu('itsec_admin_bar_menu');
  $wp_admin_bar->remove_menu('wpseo-menu');

  $current_user = wp_get_current_user();
  if ($current_user->user_login != 'tardyli') $wp_admin_bar->remove_menu('updates');
});

/*
 * Set permalink
 */
add_action('after_switch_theme', function () {
  global $wp_rewrite;

  $wp_rewrite->set_permalink_structure('/%postname%/');
  $wp_rewrite->flush_rules(true);
});

/*
 * Remove unnecessary row in admin
 */
add_filter('manage_pages_columns', function ($defaults) {
  unset($defaults['comments']);

  return $defaults;
});

/*
 * Hide update page
 */
add_action('admin_init', function () {
  $current_user = wp_get_current_user();

  if ($current_user->user_login != 'tardyli') remove_submenu_page('index.php', 'update-core.php');
});

/*
 * Remove accents
 */
add_filter('sanitize_file_name', function ($filename) {
  $sanitized_filename = remove_accents($filename);
  $invalid = [' ' => '-', '%20' => '-', '_' => '-'];
  $sanitized_filename = str_replace(array_keys($invalid), array_values($invalid), $sanitized_filename);
  $sanitized_filename = preg_replace('/[^A-Za-z0-9-\. ]/', '', $sanitized_filename);
  $sanitized_filename = preg_replace('/\.(?=.*\.)/', '', $sanitized_filename);
  $sanitized_filename = preg_replace('/-+/', '-', $sanitized_filename);
  $sanitized_filename = str_replace('-.', '.', $sanitized_filename);
  $sanitized_filename = strtolower($sanitized_filename);

  return $sanitized_filename;
}, 10, 1);

/*
 * Hide CSS
 */
add_action('admin_head', function () {
  echo '<style>' . implode(',', explode(PHP_EOL, get_abb_option('hide_css'))) . '{ display: none !important; }</style>';
});

/*
 * Enable Theme menus
 */
add_theme_support('menus');

/*
 * Disable comments
 */
if (get_abb_option('disable_comments')) include('comments.php');

/*
 * remove screen options
 */
if (get_abb_option('hide_screen_options')) {
  add_filter('screen_options_show_screen', function () {
    return false;
  });
}

/*
 * remove help options
 */
if (get_abb_option('hide_help')) {
  add_filter('contextual_help', function ($old_help, $screen_id, $screen) {
    $screen->remove_help_tabs();

    return $old_help;
  }, 999, 3);
}

/*
 * Remove meta-box
 */
add_action('admin_menu', function () {
  $metaboxes = explode(PHP_EOL, get_abb_option('hide_metabox'));

  foreach ($metaboxes as $metabox) {
    $metabox = explode(',', $metabox);
    remove_meta_box($metabox[0], $metabox[1], $metabox[2]);
  }

  $metaboxes_posttype = explode(PHP_EOL, get_abb_option('hide_metabox_posttype'));

  foreach (get_post_types() as $post_type) {
    foreach ($metaboxes_posttype as $metabox) {
      $metabox = explode(',', $metabox);
      remove_meta_box($metabox[0], $post_type, $metabox[1]);
    }
  }
});

/*
 * Site health check
 */
add_filter('site_status_tests', function ($tests) {
  unset($tests['direct']['wordpress_version']);
  unset($tests['direct']['plugin_version']);
  unset($tests['direct']['theme_version']);
  unset($tests['direct']['background_updates']);
  unset($tests['async']['background_updates']);

  return $tests;
});

/*
 * Remove site health dashboard widget
 */
add_action('wp_dashboard_setup', function () {
  remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
});

/*
 * Add permissions to editors
 */
add_action('after_switch_theme', function () {
  $role = get_role('editor');

  $role->add_cap('gform_full_access');
  $role->add_cap('edit_theme_options');
});

/*
 * Allow editors to edit privacy page
 */
add_action('map_meta_cap', function ($caps, $cap, $user_id, $args) {
  if (!is_user_logged_in()) return $caps;

  if ('manage_privacy_options' === $cap) {
    $manage_name = is_multisite() ? 'manage_network' : 'manage_options';
    $caps = array_diff($caps, [$manage_name]);
  }

  return $caps;
}, 1, 4);

/*
 * Disable wp-json for non logged users
 */
add_action('init', function () {
  if (!is_user_logged_in() && strpos($_SERVER['REQUEST_URI'], 'ithemes-security') === false) {
    add_filter('rest_authentication_errors', function () {
      return new WP_Error('rest_cannot_access', __('Only authenticated users can access the REST API.', 'abb'), array('status' => rest_authorization_required_code()));
    });
  }
});

/*
 * Disable WP-Rocket optimizations
 */
add_filter('rocket_lrc_optimization', '__return_false', 999);

/*
 * When WebP is enabled: delete the original JPG/PNG after WebP conversion and update the metadata to manage the WebP file as the main image.
 */
if (defined('ENABLE_WEBP') && ENABLE_WEBP) {
  add_filter('wp_generate_attachment_metadata', function ($metadata, $post_id) {
    if (!class_exists('Timber\ImageHelper')) return $metadata;

    $mime = get_post_mime_type($post_id);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) return $metadata;

    // Ask WordPress/PHP for a higher memory ceiling for heavy image operations.
    if (function_exists('wp_raise_memory_limit')) {
      wp_raise_memory_limit('image');
    }

    $to_bytes = static function ($value) {
      if (!is_string($value) || $value === '') return 0;
      $value = trim($value);
      if ($value === '-1') return -1;

      $unit = strtolower(substr($value, -1));
      $num  = (int)$value;

      if ($unit === 'g') return $num * 1024 * 1024 * 1024;
      if ($unit === 'm') return $num * 1024 * 1024;
      if ($unit === 'k') return $num * 1024;

      return (int)$value;
    };

    $has_enough_memory_for_image = static function ($path) use ($to_bytes) {
      $image_info = @getimagesize($path);
      if (!$image_info || empty($image_info[0]) || empty($image_info[1])) {
        // If we cannot inspect dimensions, do not block conversion.
        return true;
      }

      $width    = (int)$image_info[0];
      $height   = (int)$image_info[1];
      $channels = !empty($image_info['channels']) ? (int)$image_info['channels'] : 4;

      // Conservative estimate for decode + transform + encode buffers.
      $estimated_bytes = (int)ceil($width * $height * $channels * 2.4);
      $headroom_bytes  = 64 * 1024 * 1024;

      $memory_limit = $to_bytes(ini_get('memory_limit'));
      if ($memory_limit === -1) return true;
      if ($memory_limit <= 0) return true;

      $current_usage = memory_get_usage(true);
      return ($current_usage + $estimated_bytes + $headroom_bytes) < $memory_limit;
    };

    $convert_to_webp_safe = static function ($path) use ($has_enough_memory_for_image) {
      if (!file_exists($path)) return false;
      if (!$has_enough_memory_for_image($path)) return false;

      try {
        Timber\ImageHelper::img_to_webp($path);
      } catch (\Throwable $e) {
        return false;
      } finally {
        if (function_exists('gc_collect_cycles')) gc_collect_cycles();
        if (function_exists('gc_mem_caches')) gc_mem_caches();
      }

      $webp_path = dirname($path) . '/' . pathinfo($path, PATHINFO_FILENAME) . '.webp';
      return file_exists($webp_path);
    };

    // Reliable absolute path to the original file — works for all size origins.
    $original_path = get_attached_file($post_id);
    if (!$original_path || !file_exists($original_path)) return $metadata;

    $size_dir = dirname($original_path);

    // Convert the original to WebP (kept alongside the original JPG/PNG).
    $convert_to_webp_safe($original_path);

    // Convert each intermediate size (default + custom) to WebP, then remove
    // the JPG/PNG and update the metadata so WordPress manages the .webp file.
    $image_to_delete = [];

    if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
      // webp creation
      foreach ($metadata['sizes'] as $size_name => &$size_data) {
        $src_path  = $size_dir . '/' . $size_data['file'];
        $webp_name = pathinfo($size_data['file'], PATHINFO_FILENAME) . '.webp';
        $webp_path = $size_dir . '/' . $webp_name;

        if (!file_exists($src_path)) continue;

        $converted = $convert_to_webp_safe($src_path);

        if ($converted && file_exists($webp_path)) {
          $image_to_delete[]      = $src_path;
          $size_data['file']      = $webp_name;
          $size_data['mime-type'] = 'image/webp';
        }
      }

      // jpg/png deletion (after creation of all webp)
      foreach ($image_to_delete as $src_path) {
        @unlink($src_path);
      }

      unset($size_data);
    }

    return $metadata;
  }, 10, 2);

  // Delete the original WebP when the attachment is removed.
  // Intermediate WebP files are handled automatically via the updated metadata.
  add_action('delete_attachment', function ($post_id) {
    $file = get_attached_file($post_id);
    if (!$file) return;

    $webp = dirname($file) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.webp';
    if (file_exists($webp)) @unlink($webp);
  });
}
