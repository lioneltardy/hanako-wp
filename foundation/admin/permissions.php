<?php

if (! defined('ABSPATH')) exit;

/** Role and capability changes. */
add_action('after_switch_theme', function () {
  $role = get_role('editor');
  if (!$role) return;

  $role->add_cap('gform_full_access');
  $role->add_cap('edit_theme_options');
});

add_action('map_meta_cap', function ($caps, $cap) {
  if (!is_user_logged_in()) return $caps;

  if ($cap === 'manage_privacy_options') {
    $required_capability = is_multisite() ? 'manage_network' : 'manage_options';
    $caps = array_diff($caps, [$required_capability]);
  }

  return $caps;
}, 1, 4);
