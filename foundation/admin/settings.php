<?php

namespace HanakoWP\Admin;

if (! defined('ABSPATH')) exit;

class SettingsPage {
  /**
   * Holds the values to be used in the fields callbacks
   */
  private array $options = [];

  /**
   * Start up
   */
  public function __construct() {
    $this->set_default_options();
    add_action('admin_menu', [$this, 'add_plugin_page']);
    add_action('admin_init', [$this, 'page_init']);
  }

  /**
   * Add options page
   */
  public function add_plugin_page() {
    add_menu_page(
      'Settings Admin',
      'Configuration',
      'manage_options',
      'abb_options',
      [$this, 'create_admin_page'],
      '',
      2
    );
  }

  /**
   * Options page callback
   */
  public function create_admin_page() {
    $active_tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'frontend';

    // Set class property
    $this->options['frontend'] = get_option('abb_options_frontend');
    if (!is_array($this->options['frontend'])) $this->options['frontend'] = [];

    $this->options['backend'] = get_option('abb_options_backend');
    if (!is_array($this->options['backend'])) $this->options['backend'] = [];

    $this->options['tinymce'] = get_option('abb_options_tinymce');
    if (!is_array($this->options['tinymce'])) $this->options['tinymce'] = [];

    $this->options['vendor'] = get_option('abb_options_vendor');
    if (!is_array($this->options['vendor'])) $this->options['vendor'] = [];

?>
    <div class="wrap">
      <h1>Paramétrage du site</h1>

      <h2 class="nav-tab-wrapper">
        <a href="?page=abb_options&amp;tab=frontend" class="nav-tab <?php echo $active_tab == 'frontend' ? 'nav-tab-active' : ''; ?>">Frontend</a>
        <a href="?page=abb_options&amp;tab=backend" class="nav-tab <?php echo $active_tab == 'backend' ? 'nav-tab-active' : ''; ?>">Backend</a>
        <a href="?page=abb_options&amp;tab=tinymce" class="nav-tab <?php echo $active_tab == 'tinymce' ? 'nav-tab-active' : ''; ?>">TinyMCE</a>
        <a href="?page=abb_options&amp;tab=vendor" class="nav-tab <?php echo $active_tab == 'vendor' ? 'nav-tab-active' : ''; ?>">Vendor</a>
      </h2>

      <form method="post" action="options.php">
        <?php
        switch ($active_tab) {
          case 'frontend':
            $this->panel_frontend();
            break;
          case 'backend':
            $this->panel_backend();
            break;
          case 'tinymce':
            $this->panel_tinymce();
            break;
          case 'vendor':
            $this->panel_vendor();
            break;
        }

        if ($active_tab != 'home') submit_button();
        ?>
      </form>
    </div>
<?php
  }

  private function panel_frontend() {
    settings_fields('options_frontend');
    do_settings_sections('options_frontend');
  }

  private function panel_backend() {
    settings_fields('options_backend');
    do_settings_sections('options_backend');
  }

  private function panel_tinymce() {
    settings_fields('options_tinymce');
    do_settings_sections('options_tinymce');
  }

  private function panel_vendor() {
    settings_fields('options_vendor');
    do_settings_sections('options_vendor');
  }

  /**
   * Register and add settings
   */
  public function page_init() {
    register_setting(
      'options_frontend',
      'abb_options_frontend',
      [$this, 'sanitize_frontend']
    );

    add_settings_section(
      'options_frontend_main',
      'Paramètres généraux',
      [$this, 'print_section_info'],
      'options_frontend'
    );

    add_settings_field(
      'show_admin_bar',
      'Barre d\'administration',
      [$this, 'checkbox_callback'],
      'options_frontend',
      'options_frontend_main',
      ['frontend', 'show_admin_bar', 'Activer la barre d\'administration de wordpress']
    );

    add_settings_field(
      'dev_mode',
      'Mode développement',
      [$this, 'checkbox_callback'],
      'options_frontend',
      'options_frontend_main',
      ['frontend', 'dev_mode', 'Activer le mode développement']
    );

    add_settings_field(
      'error_reporting',
      'Nivaux d\'erreurs',
      [$this, 'select_callback'],
      'options_frontend',
      'options_frontend_main',
      ['frontend', 'error_reporting', ['Ne rien afficher', 'Tout afficher', 'Afficher les erreurs', 'Afficher les avertissements', 'Afficher les notices', 'Afficher les déprécié']]
    );

    add_settings_section(
      'options_frontend_visibility',
      'Redirection',
      [$this, 'print_section_info'],
      'options_frontend'
    );

    add_settings_field(
      'hide_site',
      'Visibilité du site',
      [$this, 'checkbox_callback'],
      'options_frontend',
      'options_frontend_visibility',
      ['frontend', 'hide_site', 'Masquer le site aux utilisateurs non connectés.']
    );

    add_settings_field(
      'redirect_to',
      'Redirection',
      [$this, 'text_callback'],
      'options_frontend',
      'options_frontend_visibility',
      ['frontend', 'redirect_to', '']
    );

    add_settings_field(
      'allowed_urls',
      'Adresses autorisées',
      [$this, 'text_callback'],
      'options_frontend',
      'options_frontend_visibility',
      ['frontend', 'allowed_urls', 'Séparer les urls par des virgules']
    );

    //backend
    register_setting(
      'options_backend',
      'abb_options_backend',
      [$this, 'sanitize_backend']
    );

    add_settings_section(
      'options_backend_main',
      'Paramètres généraux',
      [$this, 'print_section_info'],
      'options_backend'
    );

    add_settings_field(
      'disable_comments',
      'Commentaires',
      [$this, 'checkbox_callback'],
      'options_backend',
      'options_backend_main',
      ['backend', 'disable_comments', 'Désactiver les commentaires']
    );


    add_settings_section(
      'options_backend_hide',
      'Eléments à masquer',
      [$this, 'print_section_info'],
      'options_backend'
    );

    add_settings_field(
      'hide_screen_options',
      'Options d\'écran',
      [$this, 'checkbox_callback'],
      'options_backend',
      'options_backend_hide',
      ['backend', 'hide_screen_options', 'Masquer les options d\'écran']
    );

    add_settings_field(
      'hide_help',
      'Aide',
      [$this, 'checkbox_callback'],
      'options_backend',
      'options_backend_hide',
      ['backend', 'hide_help', 'Masquer l\'aide']
    );

    add_settings_field(
      'hide_metabox',
      'Masquage metabox',
      [$this, 'textarea_callback'],
      'options_backend',
      'options_backend_hide',
      ['backend', 'hide_metabox', '<small>Une metabox par ligne</small>']
    );

    add_settings_field(
      'hide_metabox_posttype',
      'Masquage metabox PT',
      [$this, 'textarea_callback'],
      'options_backend',
      'options_backend_hide',
      ['backend', 'hide_metabox_posttype', '<small>Réccursif pour chaque type de poste. Une metabox par ligne</small>']
    );

    add_settings_field(
      'hide_css',
      'Masquage CSS',
      [$this, 'textarea_callback'],
      'options_backend',
      'options_backend_hide',
      ['backend', 'hide_css', '<small>Une query CSS par ligne</small>']
    );

    //tinymce
    register_setting(
      'options_tinymce',
      'abb_options_tinymce',
      [$this, 'sanitize_tinymce']
    );

    add_settings_section(
      'options_tinymce_main',
      'Paramètres',
      [$this, 'print_section_info'],
      'options_tinymce'
    );

    add_settings_field(
      'paste_as_text',
      'Coller',
      [$this, 'checkbox_callback'],
      'options_tinymce',
      'options_tinymce_main',
      ['tinymce', 'paste_as_text', 'Coller comme texte']
    );

    add_settings_field(
      'block_formats',
      'Formats',
      [$this, 'text_callback'],
      'options_tinymce',
      'options_tinymce_main',
      ['tinymce', 'block_formats', '']
    );

    add_settings_field(
      'style_formats',
      'Styles',
      [$this, 'textarea_callback'],
      'options_tinymce',
      'options_tinymce_main',
      ['tinymce', 'style_formats', '']
    );

    add_settings_section(
      'options_tinymce_toolbars',
      'Barres d\'outils',
      [$this, 'print_section_info'],
      'options_tinymce'
    );

    add_settings_field(
      'toolbars',
      'Barres d\'outils',
      [$this, 'textarea_callback'],
      'options_tinymce',
      'options_tinymce_toolbars',
      ['tinymce', 'toolbars', '<small>Une barre d\'outil par ligne.</small>']
    );

    //vendor
    register_setting(
      'options_vendor',
      'abb_options_vendor',
      [$this, 'sanitize_vendor']
    );

    add_settings_section(
      'options_vendor_externals',
      'Ressources externes',
      [$this, 'print_section_info'],
      'options_vendor'
    );

    add_settings_field(
      'externals_css',
      'CSS externes',
      [$this, 'textarea_callback'],
      'options_vendor',
      'options_vendor_externals',
      ['vendor', 'externals_css', '<strong>{template_directory}</strong>: ' . get_bloginfo('template_directory') . '<br><strong>{dev}:</strong> ?time=' . date('U') . '<br><small>Une URL par ligne</small>']
    );

    add_settings_field(
      'externals_scripts',
      'JS externes',
      [$this, 'textarea_callback'],
      'options_vendor',
      'options_vendor_externals',
      ['vendor', 'externals_scripts', '<strong>{template_directory}</strong>: ' . get_bloginfo('template_directory') . '<br><strong>{dev}:</strong> ?time' . date('U') . '<br><small>Une URL par ligne</small>']
    );
  }

  /**
   * Sanitize frontend settings.
   *
   * @param array $input Contains all settings fields as array keys
   * @return array
   */
  public function sanitize_frontend($input) {
    if (!is_array($input)) return [];

    $sanitized = [];

    $sanitized['show_admin_bar'] = !empty($input['show_admin_bar']) ? 1 : 0;
    $sanitized['dev_mode'] = !empty($input['dev_mode']) ? 1 : 0;
    $sanitized['hide_site'] = !empty($input['hide_site']) ? 1 : 0;

    $sanitized['error_reporting'] = isset($input['error_reporting']) ? absint($input['error_reporting']) : 0;
    if ($sanitized['error_reporting'] < 0 || $sanitized['error_reporting'] > 5) {
      $sanitized['error_reporting'] = 0;
    }

    $sanitized['redirect_to'] = !empty($input['redirect_to']) ? esc_url_raw($input['redirect_to']) : '';
    $sanitized['allowed_urls'] = isset($input['allowed_urls']) ? $this->sanitize_url_list($input['allowed_urls']) : '';

    return $sanitized;
  }

  /**
   * Sanitize backend settings.
   *
   * @param array $input Contains all settings fields as array keys
   * @return array
   */
  public function sanitize_backend($input) {
    if (!is_array($input)) return [];

    $sanitized = [];

    $sanitized['disable_comments'] = !empty($input['disable_comments']) ? 1 : 0;
    $sanitized['hide_screen_options'] = !empty($input['hide_screen_options']) ? 1 : 0;
    $sanitized['hide_help'] = !empty($input['hide_help']) ? 1 : 0;
    $sanitized['hide_metabox'] = isset($input['hide_metabox']) ? $this->sanitize_multiline_text($input['hide_metabox']) : '';
    $sanitized['hide_metabox_posttype'] = isset($input['hide_metabox_posttype']) ? $this->sanitize_multiline_text($input['hide_metabox_posttype']) : '';
    $sanitized['hide_css'] = isset($input['hide_css']) ? $this->sanitize_multiline_text($input['hide_css']) : '';

    return $sanitized;
  }

  /**
   * Sanitize TinyMCE settings.
   *
   * @param array $input Contains all settings fields as array keys
   * @return array
   */
  public function sanitize_tinymce($input) {
    if (!is_array($input)) return [];

    $sanitized = [];

    $sanitized['paste_as_text'] = !empty($input['paste_as_text']) ? 1 : 0;
    $sanitized['block_formats'] = isset($input['block_formats']) ? sanitize_text_field($input['block_formats']) : '';
    $sanitized['style_formats'] = isset($input['style_formats']) ? $this->sanitize_multiline_text($input['style_formats']) : '';
    $sanitized['toolbars'] = isset($input['toolbars']) ? $this->sanitize_multiline_text($input['toolbars']) : '';

    return $sanitized;
  }

  /**
   * Sanitize vendor settings.
   *
   * @param array $input Contains all settings fields as array keys
   * @return array
   */
  public function sanitize_vendor($input) {
    if (!is_array($input)) return [];

    $sanitized = [];

    $sanitized['externals_css'] = isset($input['externals_css']) ? $this->sanitize_url_list($input['externals_css']) : '';
    $sanitized['externals_scripts'] = isset($input['externals_scripts']) ? $this->sanitize_url_list($input['externals_scripts']) : '';

    return $sanitized;
  }

  private function sanitize_url_list($value) {
    if (!is_string($value)) {
      return '';
    }

    $lines = preg_split('/[\r\n,]+/', $value);
    $clean = [];

    foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '') continue;

      if (str_starts_with($line, '/')) {
        $clean[] = '/' . ltrim($line, '/');
        continue;
      }

      $url = esc_url_raw($line);
      if ($url !== '') {
        $clean[] = $url;
      }
    }

    return implode("\n", $clean);
  }

  private function sanitize_multiline_text($value) {
    if (!is_string($value)) {
      return '';
    }

    return trim(wp_strip_all_tags($value));
  }

  public function set_default_options() {
    if (!get_option('abb_options_set')) {
      update_option('blogdescription', '');
      update_option('timezone_string', 'Europe/Zurich');
      update_option('default_pingback_flag', 0);
      update_option('thread_comments_depth', 1);
      update_option('default_ping_status', 'closed');
      update_option('default_comment_status', 'closed');
      update_option('thread_comments', 0);

      update_option('abb_options_set', true);

      update_option('abb_options_frontend', [
        'hide_site' => true,
        'dev_mode' => true,
        'error_reporting' => 1,
        'allowed_urls' => '/wp-admin/',
        'redirect_to' => '/wp-admin/'
      ]);

      update_option('dev_mode', true);

      update_option('abb_options_tinymce', [
        'toolbars' => "[\n  {\n    \"title\": \"Simple\",\n    \"data\": [\"formatselect\", \"styleselect\", \"bold\" , \"italic\" , \"bullist\", \"numlist\", \"outdent\", \"indent\", \"link\", \"unlink\", \"undo\", \"redo\", \"removeformat\"]\n  }, {\n    \"title\": \"Links\",\n    \"data\": [\"link\", \"unlink\", \"undo\", \"redo\", \"removeformat\"]\n  }\n]",
        'paste_as_text' => true,
        'block_formats' => 'Paragraphe=p;Sous-titre=h2',
        'style_formats' => "[\n  {\n    \"title\": \"Style 1\",\n    \"block\": \"p\",\n    \"classes\": \"\"\n  }\n]"
      ]);

      update_option('abb_options_backend', [
        'disable_comments' => true,
        'hide_screen_options' => true,
        'hide_metabox' => "itsec-dashboard-widget,dashboard,side,\nrg_forms_dashboard,dashboard, side\nlinkxfndiv,link,normal\nlinkadvanceddiv,link,normal'\ndashboard_quick_press,dashboard,side\ndashboard_plugins,dashboard,side\ndashboard_incoming_links,dashboard,side\ndashboard_recent_drafts,dashboard,side\nicl_dashboard_widget,dashboard,side\ndashboard_recent_comments,dashboard,normal\ndashboard_activity,dashboard,side\nwpseo-dashboard-overview,dashboard,side\ndashboard_incoming_links,dashboard,normal\ndashboard_primary,dashboard,side\ndashboard_secondary,dashboard,side\ndashboard_right_now,dashboard,side",
        'hide_metabox_posttype' => "categorydiv,side\ncommentsdiv,side\nrevisionsdiv,side\ncryptx,advanced\nrocket_post_exclude,side",
        'hide_css' => "#wp-admin-bar-WPML_ALS img\n.user-rich-editing-wrap\n.user-admin-color-wrap\n.user-comment-shortcuts-wrap\n.user-admin-bar-front-wrap\n.user-language-wrap\n.user-url-wrap\n.user-googleplus-wrap\n.user-twitter-wrap\n.user-facebook-wrap\n.user-description-wrap\n.setting[data-setting=\"description\"]\n.setting[data-setting=\"alt\"]\n.misc-pub-revisions\n#tagsdiv-client"
      ]);
    }
  }

  /**
   * Print the Section text
   */
  public function print_section_info() {
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function checkbox_callback(array $args) {
    if (!isset($args[0], $args[1], $args[2])) {
      return;
    }

    $group = sanitize_key((string) $args[0]);
    $key = sanitize_key((string) $args[1]);
    $option = isset($this->options[$group][$key]) ? $this->options[$group][$key] : false;
    $id = esc_attr($group . '_' . $key);
    $name = 'abb_options_' . $group . '[' . $key . ']';

    echo '<input type="checkbox" id="' . $id . '" name="' . esc_attr($name) . '" value="1" ' . checked(1, $option, false) . '>';
    echo '<label for="' . $id . '">' . wp_kses_post($args[2]) . '</label>';
  }

  public function text_callback(array $args) {
    if (!isset($args[0], $args[1], $args[2])) {
      return;
    }

    $group = sanitize_key((string) $args[0]);
    $key = sanitize_key((string) $args[1]);
    $option = isset($this->options[$group][$key]) ? $this->options[$group][$key] : false;
    $id = esc_attr($group . '_' . $key);
    $name = 'abb_options_' . $group . '[' . $key . ']';

    echo '<input class="large-text" id="' . $id . '" name="' . esc_attr($name) . '" value="' . esc_attr((string) $option) . '">';
    echo '<label for="' . $id . '">' . wp_kses_post($args[2]) . '</label>';
  }

  public function textarea_callback(array $args) {
    if (!isset($args[0], $args[1], $args[2])) {
      return;
    }

    $group = sanitize_key((string) $args[0]);
    $key = sanitize_key((string) $args[1]);
    $option = isset($this->options[$group][$key]) ? $this->options[$group][$key] : false;
    $id = esc_attr($group . '_' . $key);
    $name = 'abb_options_' . $group . '[' . $key . ']';

    echo '<textarea class="large-text code" rows="10" id="' . $id . '" name="' . esc_attr($name) . '">' . esc_textarea((string) $option) . '</textarea>';
    echo '<label for="' . $id . '">' . wp_kses_post($args[2]) . '</label>';
  }

  public function textarea_readonly_callback(array $args) {
    if (!isset($args[0], $args[1], $args[2])) {
      return;
    }

    $group = sanitize_key((string) $args[0]);
    $key = sanitize_key((string) $args[1]);
    $option = '';

    if (is_array($GLOBALS[$key] ?? null)) {
      foreach ($GLOBALS[$key] as $entry) {
        $option .= $entry . "\n";
      }
    }

    $id = esc_attr($group . '_' . $key);
    $name = 'abb_options_' . $group . '[' . $key . ']';

    echo '<textarea readonly class="large-text code" rows="10" id="' . $id . '" name="' . esc_attr($name) . '">' . esc_textarea($option) . '</textarea>';
    echo '<label for="' . $id . '">' . wp_kses_post($args[2]) . '</label>';
  }

  public function select_callback(array $args) {
    if (!isset($args[0], $args[1], $args[2])) {
      return;
    }

    $group = sanitize_key((string) $args[0]);
    $key = sanitize_key((string) $args[1]);
    $option = isset($this->options[$group][$key]) ? $this->options[$group][$key] : false;
    $id = esc_attr($group . '_' . $key);
    $name = 'abb_options_' . $group . '[' . $key . ']';

    echo '<select id="' . $id . '" name="' . esc_attr($name) . '">';
    foreach ($args[2] as $choice_key => $choice_value) {
      $choice_key = (string) $choice_key;
      echo '<option value="' . esc_attr($choice_key) . '" ' . selected($choice_key, $option, false) . '>' . esc_html((string) $choice_value) . '</option>';
    }
    echo '</select>';
  }
}

if (is_admin() && current_user_can('administrator')) new SettingsPage();
