<?php

if (! defined('ABSPATH')) exit;

/** Disable WPML language selector CSS */
define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);

/** Disable some default WP scripts and styles */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');

/** remove usless stuff */
add_action('wp_footer', function () {
  wp_dequeue_script('wp-embed');
});

/** Fix OB Flush bug */
remove_action('shutdown', 'wp_ob_end_flush_all', 1);
add_action('shutdown', function () {
  while (@ob_end_flush());
});

/**
 * Disable the emoji's
 */
add_action('init', function () {
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('admin_print_scripts', 'print_emoji_detection_script');
  remove_action('wp_print_styles', 'print_emoji_styles');
  remove_action('admin_print_styles', 'print_emoji_styles');
  remove_filter('the_content_feed', 'wp_staticize_emoji');
  remove_filter('comment_text_rss', 'wp_staticize_emoji');
  remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
});
