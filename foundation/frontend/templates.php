<?php

if (! defined('ABSPATH')) exit;

/** Template resolution and frontend cleanup. */
$template_types = ['index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'embed', 'home', 'frontpage', 'privacypolicy', 'page', 'paged', 'search', 'single', 'singular', 'attachment'];

foreach ($template_types as $template_type) {
  add_filter($template_type . '_template_hierarchy', function ($templates) {
    foreach ($templates as &$template) {
      if (!str_starts_with($template, 'models/')) {
        $template = 'models/' . $template;
      }
    }
    unset($template);

    return $templates;
  });
}

/** Load theme textdomain. */
add_action('after_setup_theme', function () {
  load_theme_textdomain('hw-theme', get_template_directory() . '/languages');
});

/** Remove unnecessary actions and filters. */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'rsd_link');
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

add_filter('wp_img_tag_add_auto_sizes', '__return_false');
remove_filter('wp_robots', 'wp_robots_max_image_preview_large');
