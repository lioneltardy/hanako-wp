<?php
/*
 * Gravity Forms with TailwindsCSS
 */

if (! defined('ABSPATH')) exit;

/** Get form field default class */
function hw_get_form_fragment(string $field_type, string $subset = 'default'): string {
  $default_class = [];

  switch ($field_type) {
    case 'input':
      $default_class['default'] = 'peer block w-full rounded-6 bg-gray-200 px-12 py-6 text-white outline-1 -outline-offset-1 outline-gray-500 placeholder:text-gray-500 disabled:outline-gray-200 disabled:placeholder-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-primary user-invalid:outline-red user-invalid:focus:outline-red';
      break;
    case 'textarea':
      $default_class['default'] = 'peer block w-full rounded-6 bg-gray-200 px-12 py-6 text-white outline-1 -outline-offset-1 outline-gray-500 placeholder:text-gray-500 disabled:outline-gray-200 disabled:placeholder-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-primary user-invalid:outline-red user-invalid:focus:outline-red';
      break;
    case 'select':
      $default_class['default'] = 'peer appearance-none block col-start-1 row-start-1 w-full rounded-6 bg-gray-200 px-12 py-6 text-white outline-1 -outline-offset-1 outline-gray-500 placeholder:text-gray-500 disabled:outline-gray-200 disabled:text-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-primary user-invalid:outline-red user-invalid:focus:outline-red';
      break;
    case 'select_svg':
      $default_class['default'] = '<svg viewBox="0 0 16 16" fill="currentColor" data-slot="icon" aria-hidden="true" class="pointer-events-none absolute top-5 right-0 mr-2 size-24 self-center justify-self-end"><path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" fill-rule="evenodd" /></svg>';
      break;
    case 'checkbox':
      $default_class['default'] = 'size-16 appearance-none rounded-3 border border-gray-500 bg-gray-200 checked:border-primary checked:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:border-gray-200 disabled:bg-white/10 disabled:checked:bg-gray-200 user-invalid:border-red user-invalid:focus:outline-red forced-colors:appearance-auto';
      break;
    case 'checkbox_svg':
      $default_class['default'] = '<svg viewBox="0 0 14 14" fill="none" class="pointer-events-none absolute top-3 left-0 size-16 stroke-white group-has-disabled:stroke-gray-300"><path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-checked:opacity-100" /></svg>';
      break;
    case 'radio_checkbox_container':
      $default_class['default'] = 'relative group flex gap-12 items-center';
      break;
    case 'radio':
      $default_class['default'] = 'relative grow-0 shrink-0 size-16 appearance-none rounded-full border border-gray-500 bg-gray-200 before:absolute before:inset-3 before:rounded-full before:bg-white not-checked:before:hidden checked:border-primary checked:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:border-gray-200 disabled:bg-white/10 disabled:checked:bg-gray-200 user-invalid:border-red user-invalid:focus:outline-red forced-colors:appearance-auto';
      break;
    case 'radio_label':
      $default_class['default'] = 'text-white';
      break;
    case 'label':
      $default_class['default'] =  'block mb-8';
      break;
    case 'legend':
      $default_class['default'] =  'block mb-8';
      break;
    case 'description':
      $default_class['default'] =  'mt-4 text-gray-400 text-small';
      break;
    case 'required_asterisk':
      $default_class['default'] =  'ml-4 text-red';
      break;
    case 'section_title':
      $default_class['default'] =  'border-b border-gray-200 mb-16 pb-8 font-headline text-medium';
      break;
    case 'section_description':
      $default_class['default'] =  '';
      break;
    case 'button':
      $default_class['default'] = 'inline-flex justify-center items-center rounded-6 focus-visible:outline-2 focus-visible:outline-offset-2 cursor-pointer';
      $default_class['primary'] = 'bg-primary text-white focus-visible:outline-primary disabled:bg-gray-200 disabled:text-gray-400';
      $default_class['discrete'] = 'bg-transparent text-gray-500 focus-visible:outline-gray-500 disabled:text-gray-300';
      $default_class['sm'] =  'px-8 pb-2';
      $default_class['md'] =  'px-12 py-4';
      break;
  }

  return $default_class[$subset] ?? $default_class['default'] ?? '';
}

add_filter('timber/twig/functions', function ($functions) {
  $functions['get_form_fragment'] = ['callable' => 'hw_get_form_fragment'];

  return $functions;
});

/** Reset the Gravity Forms width classes in the form editor so only the column layout appears. */
add_action('admin_head', function () {
  echo '<style>
	.gform_wrapper.gravity-theme .gfield input.small, .gform_wrapper.gravity-theme .gfield select.small,
	.gform_wrapper.gravity-theme .gfield input.medium, .gform_wrapper.gravity-theme .gfield select.medium {
		width: 100% !important;
	}</style>';
});

/** Display zipcode before city in Address Fields */
add_filter('gform_address_display_format', function () {
  return 'zip_before_city';
}, 10, 2);

if (! is_admin()) {

  /** Disable Gravity Forms CSS. */
  add_filter('pre_option_rg_gforms_disable_css', '__return_true');

  /** Enable HTML5. */
  add_filter('pre_option_rg_gforms_enable_html5', '__return_true');

  /** Remove required legend. */
  add_filter('gform_required_legend', '__return_empty_string');

  /** Add class to .gform_fields. */
  add_filter('gform_get_form_filter', function (string $form_string) {
    $form_string = str_replace('class=\'gform_fields', 'class=\'gform_fields grid gap-16', $form_string);

    return $form_string;
  }, 10, 2);

  /** Add column & margin classes to .gform_field. */
  add_filter('gform_field_container', function (string $field_container) {
    $field_container = str_replace('gfield--width-quarter', 'col-span-12 lg:col-span-3', $field_container);
    $field_container = str_replace('gfield--width-third', 'col-span-12 lg:col-span-4', $field_container);
    $field_container = str_replace('gfield--width-five-twelfths', 'col-span-12 lg:col-span-5', $field_container);
    $field_container = str_replace('gfield--width-half', 'col-span-12 lg:col-span-6', $field_container);
    $field_container = str_replace('gfield--width-seven-twelfths', 'col-span-12 lg:col-span-7', $field_container);
    $field_container = str_replace('gfield--width-two-thirds', 'col-span-12 lg:col-span-8', $field_container);
    $field_container = str_replace('gfield--width-three-quarter', 'col-span-12 lg:col-span-9', $field_container);
    $field_container = str_replace('gfield--width-five-sixths', 'col-span-12 lg:col-span-10', $field_container);
    $field_container = str_replace('gfield--width-eleven-twelfths', 'col-span-12 lg:col-span-11', $field_container);
    $field_container = str_replace('gfield--width-full', 'col-span-12', $field_container);
    $field_container = str_replace('gfield--type-section', 'gfield--type-section col-span-12', $field_container);

    return $field_container;
  }, 10, 6);

  /** Modify the field classes to Bootstrap classes. */
  add_filter('gform_field_content', function ($field_content, $field) {

    // Required
    $field_content = str_replace('gfield_required_asterisk', 'gfield_required_asterisk ' . hw_get_form_fragment('required_asterisk'), $field_content);

    // Select fields.
    $field_content = str_replace('class=\'small gfield_select', 'class=\'gfield_select ' . hw_get_form_fragment('select'), $field_content);
    $field_content = str_replace('class=\'medium gfield_select', 'class=\'gfield_select ' . hw_get_form_fragment('select'), $field_content);
    $field_content = str_replace('class=\'large gfield_select', 'class=\'gfield_select ' . hw_get_form_fragment('select'), $field_content);
    $field_content = str_replace('ginput_container_select', 'ginput_container_select relative ', $field_content);
    $field_content = str_replace('</select>', '</select>' . hw_get_form_fragment('select_svg'), $field_content);

    // Add .form-control to most inputs.
    $field_content = str_replace('class=\'small', 'class=\'' . hw_get_form_fragment('input'), $field_content);
    $field_content = str_replace('class=\'medium', 'class=\'' . hw_get_form_fragment('input'), $field_content);
    $field_content = str_replace('class=\'large', 'class=\'' . hw_get_form_fragment('input'), $field_content);

    // Textarea fields.
    $field_content = str_replace('class=\'textarea small', 'class=\'textarea ' . hw_get_form_fragment('textarea'), $field_content);
    $field_content = str_replace('class=\'textarea medium', 'class=\'textarea ' . hw_get_form_fragment('textarea'), $field_content);
    $field_content = str_replace('class=\'textarea large', 'class=\'textarea ' . hw_get_form_fragment('textarea'), $field_content);

    $rows = 3;

    switch ($field['textareaHeight']) {
      case 'medium':
        $rows = 6;
        break;
      case 'large':
        $rows = 9;
        break;
    }

    $field_content = str_replace('rows=\'10\'', 'rows=\'' . $rows . '\'', $field_content);

    // Labels.
    if ($field['labelPlacement'] === 'hidden_label') {
      $field_content = str_replace('gfield_label', 'gfield_label hidden', $field_content);
    } else {
      $field_content = str_replace('gfield_label gform-field-label', 'gfield_label gform-field-label ' . hw_get_form_fragment('label'), $field_content);
    }

    // Sub-Labels.
    if ($field['subLabelPlacement'] === 'hidden_label') {
      $field_content = str_replace('hidden_sub_label', 'hidden_sub_label hidden', $field_content);
    } else {
      $field_content = str_replace('gform-field-label--type-sub', 'gform-field-label--type-sub ' . hw_get_form_fragment('label'), $field_content);
    }

    // Descriptions & instruction
    $field_content = str_replace('class=\'gfield_description\'', 'class=\'gfield_description ' . hw_get_form_fragment('description') . '\'', $field_content);
    $field_content = str_replace('class=\'gfield_description instruction ', 'class=\'gfield_description instruction ' . hw_get_form_fragment('description'), $field_content);

    // Sections
    $field_content = str_replace('gsection_title', 'gsection_title ' . hw_get_form_fragment('section_title'), $field_content);
    $field_content = str_replace('class=\'gsection_description', 'class=\'gsection_description  ' . hw_get_form_fragment('section_description'), $field_content);

    // Checkbox
    if ($field['type'] == 'checkbox' || $field['inputType'] == 'checkbox') {
      if ($field['choiceAlignment'] == 'horizontal') $field_content = str_replace('gfield_checkbox ', 'gfield_checkbox flex gap-16 ', $field_content);
      if ($field['choiceAlignment'] == 'columns') $field_content = str_replace('gfield_checkbox ', 'gfield_checkbox ' . hw_get_gf_choice_columns($field['displayColumns']) . ' ', $field_content);

      $field_content = str_replace('class=\'gchoice ', 'class=\'gchoice ' . hw_get_form_fragment('radio_checkbox_container') . ' ', $field_content);
      $field_content = str_replace('gfield-choice-input', 'gfield-choice-input ' . hw_get_form_fragment('checkbox'), $field_content);
      $field_content = str_replace('gform-field-label', 'gform-field-label form-check-label', $field_content);
      $field_content = preg_replace('/<input([^>]+[^>]+)>/i', '<input$1>' . hw_get_form_fragment('checkbox_svg'), $field_content);
      $field_content = str_replace('type="button"', 'type="button" class="my-8 ' . hw_get_form_fragment('button', 'default') . ' ' . hw_get_form_fragment('button', 'primary') . ' ' . hw_get_form_fragment('button', 'sm') .  '"', $field_content);
    }

    // Radio
    if ($field['type'] == 'radio' || $field['inputType'] == 'radio') {
      if ($field['choiceAlignment'] == 'horizontal') $field_content = str_replace('gfield_radio', 'gfield_radio flex gap-16 ', $field_content);
      if ($field['choiceAlignment'] == 'columns') $field_content = str_replace('gfield_radio', 'gfield_radio' . hw_get_gf_choice_columns($field['displayColumns']) . ' ', $field_content);

      $field_content = str_replace('class=\'gchoice ', 'class=\'gchoice ' . hw_get_form_fragment('radio_checkbox_container') . ' ', $field_content);
      $field_content = str_replace('gfield-choice-input', 'gfield-choice-input ' . hw_get_form_fragment('radio'), $field_content);
      $field_content = str_replace('gform-field-label', 'gform-field-label ' . hw_get_form_fragment('radio_label'), $field_content);
      $field_content = str_replace('gchoice_other_control', 'gchoice_other_control ' . hw_get_form_fragment('input'), $field_content);
    }

    // Complex fields layout.
    $field_content = str_replace('gform-grid-row', 'gform-grid-row grid-12 gap-16', $field_content);

    // Email, Post Image & Address Fields.
    $field_content = str_replace('ginput_left', 'ginput_left col-span-12 lg:col-span-6', $field_content);
    $field_content = str_replace('ginput_left col-span-12 lg:col-span-6 address_country', 'ginput_left col-span-12', $field_content);
    $field_content = str_replace('ginput_right', 'ginput_right col-span-12 lg:col-span-6', $field_content);
    $field_content = str_replace('ginput_full', 'ginput_full col-span-12', $field_content);
    $field_content = str_replace('class=\'\' type=\'email\'', 'class=\'' . hw_get_form_fragment('input') . '\' type=\'email\'', $field_content);

    if ($field['type'] === 'date' || $field['type'] === 'time') {
      $col_span = $field['type'] === 'date' ? 'col-span-4' : 'col-span-6';
      $field_content = str_replace('gform-grid-col', 'gform-grid-col ' . $col_span, $field_content);
      $field_content = str_replace('hour_minute_colon', 'hidden', $field_content);
      $field_content = str_replace('<select', '<select class=\'' . hw_get_form_fragment('select') . '\'', $field_content);
      $field_content = str_replace('type=\'number\'', 'type=\'number\' class=\'' . hw_get_form_fragment('input') . '\'', $field_content);
      $field_content = str_replace('class=\'datepicker ', 'class=\'datepicker ' . hw_get_form_fragment('input') . ' ', $field_content);
      $field_content = str_replace('ginput_container_date', 'ginput_container_date relative ' . $col_span, $field_content);
      $field_content = str_replace('gform-datepicker-toggle ', 'gform-datepicker-toggle absolute right-6 top-3 ' . $col_span, $field_content);
      $field_content = str_replace('gform-datepicker-toggle-icon ', 'gform-datepicker-toggle-icon block size-24 bg-primary ' . $col_span, $field_content);
    }

    if ($field['type'] === 'phone') {
      $field_content = '<p class="text-red">The "Phone" field type is not supported. Please use a text field with a phone number validation instead.</p>';
    }

    // Name Fields.
    if ($field['type'] === 'name') {
      $field_content = str_replace('gform-grid-col--size-auto', 'gform-grid-col--size-auto col-span-12 lg:col-span-6', $field_content);
      $field_content = str_replace('type=\'text\'', 'type=\'text\' class=\'' . hw_get_form_fragment('input') . '\'', $field_content);
      $field_content = str_replace('<select ', '<select class=\'' . hw_get_form_fragment('select') . '\' ', $field_content);
    }

    // Address Fields.
    if ('address' === $field['type']) {
      $field_content = str_replace('type=\'text\'', 'type=\'text\' class=\'' . hw_get_form_fragment('input') . '\'', $field_content);
      $field_content = str_replace('<select ', '<select class=\'' . hw_get_form_fragment('select') . '\' ', $field_content);
      $field_content = str_replace('class=\'copy_values_option_container', 'class=\'copy_values_option_container form-check', $field_content);
      $field_content = str_replace('class=\'copy_values_activated', 'class=\'copy_values_activated form-check-input', $field_content);
      $field_content = str_replace('class=\'copy_values_option_label', 'class=\'copy_values_option_label form-check-label', $field_content);
    }

    // Consent fields.
    if ('consent' === $field['type']) {
      $field_content = str_replace('ginput_container_consent', 'ginput_container_consent ' . hw_get_form_fragment('radio_checkbox_container'), $field_content);
      $field_content = str_replace('type=\'checkbox\'', 'type=\'checkbox\' class=\'' . hw_get_form_fragment('checkbox') . '\' ', $field_content);
    }

    // List fields.
    if ('list' === $field['type']) {
      $field_content = str_replace('gfield_list_group ', 'gfield_list_group flex col-span-12 items-center ', $field_content);
      $field_content = str_replace('grid-12', '', $field_content);
      $field_content = str_replace('gfield_list_groups', 'gfield_list_groups grid-12 gap-8 ', $field_content);
      $field_content = str_replace('gfield_list_group_item', 'gfield_list_group_item flex grow-1 ', $field_content);
      $field_content = str_replace('type=\'text\'', 'type=\'text\' class=\'' . hw_get_form_fragment('input') . '\'', $field_content);

      $field_content = str_replace('gfield_list_icons', 'gfield_list_icons flex gap-8', $field_content);
      $field_content = str_replace('>Add<', '>+<', $field_content);
      $field_content = str_replace('>Remove<', '>–<', $field_content);
      $field_content = str_replace('add_list_item', 'add_list_item flex justify-center items-center size-24 border border-primary rounded-full text-primary ', $field_content);
      $field_content = str_replace('delete_list_item', 'delete_list_item flex justify-center items-center size-24 border border-red rounded-full text-red ', $field_content);
    }

    // Fileupload & Post Image fields.
    if ('fileupload' === $field['type'] || 'post_image' === $field['type']) {
      $field_content = str_replace('type=\'text\'', 'type=\'text\' class=\'form-control form-control-sm\'', $field_content); // Post Image meta fields.
      $field_content = str_replace('gform_fileupload_rules', 'gform_fileupload_rules  text-small text-gray-400', $field_content);

      // Mutli file upload.
      if (true === $field['multipleFiles']) {
        $field_content = str_replace('ginput_container_fileupload', 'ginput_container_fileupload group flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-400 px-24 py-40 ', $field_content);
        $field_content = str_replace('class=\'gform_drop_instructions', 'class=\'gform_drop_instructions hidden', $field_content);
        $field_content = str_replace('gform_fileupload_rules', 'gform_fileupload_rules text-small text-gray-400', $field_content);
        $field_content = str_replace('class=\'button', 'class=\'button text-primary', $field_content);
      }
    }

    // Field error
    if ($field['failed_validation']) $field_content = str_replace('class=\'', 'class=\'!outline-red !border-red ', $field_content);

    // Validation message.
    $field_content = str_replace('gfield_validation_message', 'gfield_validation_message mt-8 text-small text-red', $field_content);

    return $field_content;
  }, 10, 5);

  /** Add class to gform_validation_errors */
  add_filter('gform_get_form_filter', function (string $form_string) {
    $form_string = str_replace('gform_validation_errors', 'gform_validation_errors mb-32 outline-0 text-red', $form_string);
    $form_string = str_replace('<ol>', '<ol class="list-disc ps-16">', $form_string);

    return $form_string;
  }, 10, 2);

  /** Add class to gform_footer */
  add_filter('gform_get_form_filter', function (string $form_string) {
    $form_string = str_replace('gform_footer', 'gform_footer flex gap-16 mt-16', $form_string);
    $form_string = str_replace('gform_page_footer', 'gform_page_footer flex gap-16 mt-16', $form_string);

    return $form_string;
  }, 10, 2);

  /** Change classes on Submit button */
  add_filter('gform_submit_button', function ($button) {
    return str_replace('class=\'gform_button', 'class=\'gform_button ' . hw_get_form_fragment('button', 'default') . ' ' . hw_get_form_fragment('button', 'primary') . ' ' . hw_get_form_fragment('button', 'md'), $button);
  }, 10, 2);

  /** Change classes on Next button */
  add_filter('gform_next_button', function ($button) {
    return str_replace('class=\'gform_next_button', 'class=\'gform_next_button ' . hw_get_form_fragment('button', 'default') . ' ' . hw_get_form_fragment('button', 'primary') . ' ' . hw_get_form_fragment('button', 'md'), $button);
  }, 10, 2);

  /** Change classes on Previous button  */
  add_filter('gform_previous_button', function ($button) {
    return str_replace('class=\'gform_previous_button', 'class=\'gform_previous_button ' . hw_get_form_fragment('button', 'default') . ' ' . hw_get_form_fragment('button', 'discrete') . ' ' . hw_get_form_fragment('button', 'md'), $button);
  }, 10, 2);

  /** Change classes on Save & Continue Later button */
  add_filter('gform_savecontinue_link', function ($button) {
    $button = str_replace('class=\'gform_save_link', 'class=\'btn ' . hw_get_form_fragment('button', 'default') . ' ' . hw_get_form_fragment('button', 'discrete') . ' ' . hw_get_form_fragment('button', 'md'), $button);
    $button = str_replace('<svg ', '<svg class="mr-8" ', $button);

    return $button;
  }, 10, 2);

  /** Change classes on Progress bars */
  add_filter('gform_progress_bar', function ($progress_bar) {
    $progress_bar = str_replace('class=\'gf_progressbar_wrapper', 'class=\'gf_progressbar_wrapper mb-32', $progress_bar);
    $progress_bar = str_replace('gf_progressbar_title', 'gf_progressbar_title mb-8', $progress_bar);
    $progress_bar = str_replace('gf_progressbar ', 'progress gf_progressbar bg-gray-200 rounded-full overflow-hidden ', $progress_bar);
    $progress_bar = str_replace('gf_progressbar_percentage', 'gf_progressbar_percentage px-8 py-2 text-small', $progress_bar);
    $progress_bar = str_replace('percentbar_blue', 'bg-primary percentbar_blue', $progress_bar);
    $progress_bar = str_replace('percentbar_gray', 'bg-gray-400 percentbar_gray', $progress_bar);
    $progress_bar = str_replace('percentbar_green', 'bg-green percentbar_green', $progress_bar);
    $progress_bar = str_replace('percentbar_orange', 'bg-orange percentbar_orange', $progress_bar);
    $progress_bar = str_replace('percentbar_red', 'bg-red percentbar_red', $progress_bar);

    return $progress_bar;
  }, 10, 3);

  /**
   * Hide Gravityforms Spinner.
   */
  function strt_ajax_spinner_url() {
    return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
  }
  add_filter('gform_ajax_spinner_url', 'strt_ajax_spinner_url');
}

function hw_get_gf_choice_columns(string $columns) {
  $columns_class = 'gap-x-16 gap-y-8 ';

  switch ($columns) {
    case 1:
      $columns_class .= 'grid-1';
      break;
    case 2:
      $columns_class .= 'grid-2';
      break;
    case 3:
      $columns_class .= 'grid-3';
      break;
    case 4:
      $columns_class .= 'grid-4';
      break;
    case 5:
      $columns_class .= 'grid-5';
      break;
  }

  return $columns_class;
}
