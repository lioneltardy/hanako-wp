<?php
/** Restrict REST API access for unauthenticated visitors. */
add_action('init', function () {
  $request_uri = wp_unslash($_SERVER['REQUEST_URI'] ?? '');

  if (!is_user_logged_in() && strpos($request_uri, 'ithemes-security') === false) {
    add_filter('rest_authentication_errors', function () {
      return new WP_Error('rest_cannot_access', __('Only authenticated users can access the REST API.', 'abb'), ['status' => rest_authorization_required_code()]);
    });
  }
});
