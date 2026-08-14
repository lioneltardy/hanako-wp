<?php

use Timber\Image;
use Timber\ImageHelper;
use Timber\Timber;

if (! defined('ABSPATH')) exit;

/** Convert an image to WebP format and optionally delete the original source image. */
function hw_to_webp(string $src, $delete_source = true) {
  global $wpdb;

  if (!defined('ENABLE_WEBP') || !ENABLE_WEBP || empty($src) || !class_exists('Timber\\ImageHelper')) {
    return $src;
  }

  $webp_src = ImageHelper::img_to_webp($src);
  if (empty($webp_src)) return $src;

  if ($delete_source) {
    $src_without_query  = strtok((string) $src, '?');
    $webp_without_query = strtok((string) $webp_src, '?');

    $upload = wp_upload_dir();
    $baseurl = $upload['baseurl'] ?? '';
    $basedir = $upload['basedir'] ?? '';

    if (!empty($baseurl) && !empty($basedir) && strpos($src_without_query, $baseurl) === 0) {
      $relative_src_path  = ltrim(substr($src_without_query, strlen($baseurl)), '/');
      $relative_webp_path = ltrim(substr($webp_without_query, strlen($baseurl)), '/');
      $source_path = rtrim($basedir, '/') . '/' . $relative_src_path;
      $webp_path   = rtrim($basedir, '/') . '/' . $relative_webp_path;
      $is_intermediate_size = (bool) preg_match('/-\\d+x\\d+\\.(jpe?g|png)$/i', basename($source_path));

      if ($is_intermediate_size) {
        $filename = basename($source_path);
        $attachment_id = $wpdb->get_var($wpdb->prepare(
          "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
          '_wp_attachment_metadata',
          '%' . $wpdb->esc_like($filename) . '%'
        ));

        if ($attachment_id) {
          $metadata = wp_get_attachment_metadata($attachment_id);
          if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as &$image_size) {
              if (isset($image_size['file']) && $image_size['file'] === basename($source_path)) {
                $image_size['file'] = basename($webp_path);
                break;
              }
            }
            unset($image_size);
            wp_update_attachment_metadata($attachment_id, $metadata);
          }
        }
      }

      if ($is_intermediate_size && file_exists($source_path) && file_exists($webp_path)) {
        @unlink($source_path);
      }
    }
  }

  return $webp_src;
}

/** Image lazy-loading helpers. */
function hw_get_srcs(Image $timber_image, string $size) {
  $srcs = [];

  foreach (['', '-2x'] as $suffix) {
    $timber_image->sizes;
    if ($suffix === '-2x' && !isset($timber_image->sizes[$size . $suffix])) continue;

    $src = $timber_image->src($size . $suffix);
    $srcs[] = ENABLE_WEBP ? hw_to_webp($src) : $src;
  }

  return $srcs;
}

function hw_sanitize_data_attributes(string $attributes): string {
  preg_match_all('/\\b(data-[a-z0-9_-]+)\\s*=\\s*(["\\\'])(.*?)\\2/i', $attributes, $matches, PREG_SET_ORDER);

  return implode(' ', array_map(function ($match) {
    return esc_attr($match[1]) . '="' . esc_attr($match[3]) . '"';
  }, $matches));
}

function hw_lazy_image(int $image, string $size, $classes = '', $alt = '', $title = '', $data = '') {
  $timber_image = Timber::get_image($image);

  if (empty($timber_image)) return;

  $ratio = $timber_image->aspect() > 0 ? 100 / $timber_image->aspect : 1;
  if (isset($timber_image->sizes[$size])) {
    $image_size = $timber_image->sizes[$size];
    $ratio = $image_size['height'] != 0 ? 100 / ($image_size['width'] / $image_size['height']) : 0;
  }

  $alt = !empty($alt) ? $alt : $timber_image->alt;
  $title = !empty($title) ? $title : $timber_image->title;
  $srcs = hw_get_srcs($timber_image, $size);
  $data_attributes = hw_sanitize_data_attributes((string) $data);

  $return  = '<div class="aspect-(--aspect-ratio) ' . esc_attr((string) $classes) . '" style="--aspect-ratio: ' . esc_attr((string) $timber_image->aspect()) . ';">';
  $return .= '<img data-hw-src="' . esc_attr(implode(';', $srcs)) . '" class="w-full h-full" title="' . esc_attr((string) $title) . '" alt="' . esc_attr((string) $alt) . '"' . ($data_attributes !== '' ? ' ' . $data_attributes : '') . '>';
  $return .= '</div>';

  return $return;
}

function hw_lazy_background_image(int $image, string $size) {
  $timber_image = Timber::get_image($image);

  if (empty($timber_image)) return;

  return 'data-hw-background-image="' . esc_attr(implode(';', hw_get_srcs($timber_image, $size))) . '"';
}
