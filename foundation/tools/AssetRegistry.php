<?php

namespace HanakoWP\Tools;

if (! defined('ABSPATH')) exit;

class AssetRegistry {
  private static ?self $instance = null;
  private array $scripts = [];
  private array $styles = [];

  private function __construct() {
  }

  public static function instance(): self {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function add_script(string $handle, string $src, bool $in_footer = false): void {
    $this->scripts[] = [$handle, $src, $in_footer];
  }

  public function add_style(string $handle, string $src, $media = false): void {
    $this->styles[] = [$handle, $src, $media];
  }

  public function enqueue(): void {
    wp_dequeue_style('wp-block-library');

    foreach ($this->styles as $style) {
      wp_register_style($style[0], $style[1], false, 1, !empty($style[2]) ? $style[2] : 'all');
      wp_enqueue_style($style[0]);
    }

    foreach ($this->scripts as $script) {
      [$handle, $src, $in_footer] = $script;

      if ($src === '') continue;

      wp_register_script($handle, $src, [], false, $in_footer);
      wp_enqueue_script($handle);

      if ($handle === 'hw-script') {
        wp_script_add_data($handle, 'type', 'module');
        wp_localize_script($handle, 'HWP', [
          'template_url' => get_stylesheet_directory_uri(),
          'ajax_url' => admin_url('admin-ajax.php')
        ]);
      }
    }
  }
}
