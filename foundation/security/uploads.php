<?php

if (! defined('ABSPATH')) exit;

/** Allow SVG upload and reject executable SVG content. */
add_filter('upload_mimes', function ($mimes) {
  $mimes['svg'] = 'image/svg+xml';

  return $mimes;
});

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
  if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'svg') return $data;

  if (!current_user_can('upload_files')) return $data;

  $data['ext'] = 'svg';
  $data['type'] = 'image/svg+xml';

  return $data;
}, 10, 4);

add_filter('wp_handle_upload_prefilter', function ($file) {
  if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'svg') return $file;

  $tmp = $file['tmp_name'] ?? null;
  if (!$tmp || !is_readable($tmp)) {
    $file['error'] = __('Fichier SVG invalide.', 'abb');

    return $file;
  }

  $content = file_get_contents($tmp);
  if ($content === false) {
    $file['error'] = __('Impossible de lire le fichier SVG.', 'abb');

    return $file;
  }

  $dangerous = [
    '/<script\b/i',
    '/<iframe\b/i',
    '/<foreignObject\b/i',
    '/on[a-z]+\s*=/i',
    '/<svg\b[^>]*onload\s*=/i',
    '/javascript\s*:/i',
    '/data\s*:\s*text\/html/i',
  ];

  foreach ($dangerous as $pattern) {
    if (preg_match($pattern, $content)) {
      $file['error'] = __('Le fichier SVG contient du contenu non autorisé.', 'abb');

      return $file;
    }
  }

  return $file;
});
