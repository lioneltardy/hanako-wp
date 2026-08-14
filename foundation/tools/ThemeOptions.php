<?php

namespace HanakoWP\Tools;

if (! defined('ABSPATH')) exit;


class ThemeOptions {
  private static ?self $instance = null;
  private array $options;

  private function __construct() {
    $this->options = array_merge(
      $this->get_option_array('abb_options_frontend'),
      $this->get_option_array('abb_options_menu'),
      $this->get_option_array('abb_options_backend'),
      $this->get_option_array('abb_options_cpt'),
      $this->get_option_array('abb_options_tinymce'),
      $this->get_option_array('abb_options_vendor')
    );
  }

  public static function instance(): self {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function get(string $key) {
    return $this->options[$key] ?? false;
  }

  private function get_option_array(string $option_name): array {
    $option = get_option($option_name);

    return is_array($option) ? $option : [];
  }
}
