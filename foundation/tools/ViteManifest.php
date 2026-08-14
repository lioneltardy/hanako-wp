<?php

namespace HanakoWP\Tools;

if (! defined('ABSPATH')) exit;

class ViteManifest {
  private static ?self $instance = null;
  private array $manifest;

  private function __construct() {
    $manifest_path = get_template_directory() . '/dist/.vite/manifest.json';
    $raw_manifest = file_exists($manifest_path) ? file_get_contents($manifest_path) : false;
    $decoded_manifest = json_decode((string) $raw_manifest, true);

    $this->manifest = is_array($decoded_manifest) ? $decoded_manifest : [];
  }

  public static function instance(): self {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function asset_path(string $entry_name): string {
    if (empty($this->manifest[$entry_name]['file'])) return '';

    return '/dist/' . ltrim($this->manifest[$entry_name]['file'], '/');
  }

  public function asset_uri(string $entry_name): string {
    $asset_path = $this->asset_path($entry_name);

    return $asset_path === '' ? '' : get_bloginfo('template_directory') . $asset_path;
  }

  public function css_uris(string $entry_name): array {
    if (empty($this->manifest[$entry_name])) return [];

    $css_files = [];
    $entry = $this->manifest[$entry_name];

    if (!empty($entry['file']) && str_ends_with($entry['file'], '.css')) {
      $css_files[] = $entry['file'];
    }

    if (!empty($entry['css']) && is_array($entry['css'])) {
      $css_files = array_merge($css_files, $entry['css']);
    }

    return array_map(function ($file) {
      return get_bloginfo('template_directory') . '/dist/' . ltrim($file, '/');
    }, array_values(array_unique($css_files)));
  }
}
