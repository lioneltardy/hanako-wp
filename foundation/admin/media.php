<?php

if (! defined('ABSPATH')) exit;

/** Don’t generate thumbnail for non-image attachments */
add_filter('fallback_intermediate_image_sizes', function () {
  return [];
});

/** Set JPEG quality to 100% */
add_filter('jpeg_quality', function () {
  return 100;
});
add_filter('big_image_size_threshold', '__return_false');

/** Remove some default image sizes */
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
  return array_filter($sizes, function ($size) {
    return $size !== 'medium_large';
  });
});

/** Sanitize file names to be lowercase and use dashes instead of spaces or underscores. */
add_filter('sanitize_file_name', function ($filename) {
  $sanitized_filename = remove_accents($filename);
  $invalid = [' ' => '-', '%20' => '-', '_' => '-'];
  $sanitized_filename = str_replace(array_keys($invalid), array_values($invalid), $sanitized_filename);
  $sanitized_filename = preg_replace('/[^A-Za-z0-9-\. ]/', '', $sanitized_filename);
  $sanitized_filename = preg_replace('/\.(?=.*\.)/', '', $sanitized_filename);
  $sanitized_filename = preg_replace('/-+/', '-', $sanitized_filename);
  $sanitized_filename = str_replace('-.', '.', $sanitized_filename);

  return strtolower($sanitized_filename);
}, 10, 1);

/** Convert uploaded images to WebP format if the ENABLE_WEBP constant is defined and set to true. */
if (defined('ENABLE_WEBP') && ENABLE_WEBP) {
  add_filter('wp_generate_attachment_metadata', function ($metadata, $post_id) {
    if (!class_exists('Timber\ImageHelper')) return $metadata;

    $mime = get_post_mime_type($post_id);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) return $metadata;

    if (function_exists('wp_raise_memory_limit')) {
      wp_raise_memory_limit('image');
    }

    $to_bytes = static function ($value) {
      if (!is_string($value) || $value === '') return 0;
      $value = trim($value);
      if ($value === '-1') return -1;

      $unit = strtolower(substr($value, -1));
      $number = (int) $value;

      if ($unit === 'g') return $number * 1024 * 1024 * 1024;
      if ($unit === 'm') return $number * 1024 * 1024;
      if ($unit === 'k') return $number * 1024;

      return (int) $value;
    };

    $has_enough_memory_for_image = static function ($path) use ($to_bytes) {
      $image_info = @getimagesize($path);
      if (!$image_info || empty($image_info[0]) || empty($image_info[1])) {
        return true;
      }

      $width = (int) $image_info[0];
      $height = (int) $image_info[1];
      $channels = !empty($image_info['channels']) ? (int) $image_info['channels'] : 4;
      $estimated_bytes = (int) ceil($width * $height * $channels * 2.4);
      $headroom_bytes = 64 * 1024 * 1024;
      $memory_limit = $to_bytes(ini_get('memory_limit'));

      if ($memory_limit === -1 || $memory_limit <= 0) return true;

      return (memory_get_usage(true) + $estimated_bytes + $headroom_bytes) < $memory_limit;
    };

    $convert_to_webp_safe = static function ($path) use ($has_enough_memory_for_image) {
      if (!file_exists($path) || !$has_enough_memory_for_image($path)) return false;

      try {
        Timber\ImageHelper::img_to_webp($path);
      } catch (\Throwable $exception) {
        return false;
      } finally {
        if (function_exists('gc_collect_cycles')) gc_collect_cycles();
        if (function_exists('gc_mem_caches')) gc_mem_caches();
      }

      $webp_path = dirname($path) . '/' . pathinfo($path, PATHINFO_FILENAME) . '.webp';

      return file_exists($webp_path);
    };

    $original_path = get_attached_file($post_id);
    if (!$original_path || !file_exists($original_path)) return $metadata;

    $size_dir = dirname($original_path);
    $convert_to_webp_safe($original_path);
    $image_to_delete = [];

    if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
      foreach ($metadata['sizes'] as &$size_data) {
        $source_path = $size_dir . '/' . $size_data['file'];
        $webp_name = pathinfo($size_data['file'], PATHINFO_FILENAME) . '.webp';
        $webp_path = $size_dir . '/' . $webp_name;

        if (!file_exists($source_path)) continue;

        if ($convert_to_webp_safe($source_path) && file_exists($webp_path)) {
          $image_to_delete[] = $source_path;
          $size_data['file'] = $webp_name;
          $size_data['mime-type'] = 'image/webp';
        }
      }
      unset($size_data);

      foreach ($image_to_delete as $source_path) {
        @unlink($source_path);
      }
    }

    return $metadata;
  }, 10, 2);

  add_action('delete_attachment', function ($post_id) {
    $file = get_attached_file($post_id);
    if (!$file) return;

    $webp = dirname($file) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.webp';
    if (file_exists($webp)) @unlink($webp);
  });
}
