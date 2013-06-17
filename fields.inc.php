<?php
// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:
/**
 * @file
 * Define the pgbar field type.
 */

function _pgbar_merge_into(&$a, $b) {
  foreach($b as $k => $v) {
    if(array_key_exists($k, $a))
      if (is_array($v))
        $a[$k] = _pgbar_merge_into($a[$k], $b[$k]);
      else
        ; //ignore value in $b
    else
      $a[$k] = $v;
  }
  return $a;
}

/**
 * Implements hook_field_info().
 */
function pgbar_field_info() {
  $info['pgbar'] = array(
    'label' => t('Progress bar'),
    'description' => t('Allows you to display a progress bar based on two numbers: target and current'),
    'settings' => array('style' => NULL),
    'default_widget' => 'pgbar',
    'default_formatter' => 'pgbar',
  );
  return $info;
}

/**
 * Implements hook_field_widget_info().
 */
function pgbar_field_widget_info() {
  $info['pgbar'] = array(
    'label' => t('Progress bar: Target number'),
    'field types' => array('pgbar'),
    'settings' => array('size' => 60),
    'behaviors' => array(
      'multiple values' => FIELD_BEHAVIOR_DEFAULT,
      'default values' => FIELD_BEHAVIOR_DEFAULT,
    ),
  );
  return $info;
}

/**
 * Implements hook_field_formatter_info().
 */
function pgbar_field_formatter_info() {
  $info['pgbar'] = array(
    'label' => 'Progress bar',
    'field types' => array('pgbar'),
  );
  return $info;
}

function _pgbar_source_plugin_load($entity, $field, $instance) {
  ctools_include('plugins');
  $plugin_name = isset($instance['settings']['source']) ? $instance['settings']['source'] : 'webform_submissions.inc';
  $plugin = ctools_get_plugins('pgbar', 'source', $plugin_name);
  $class = ctools_plugin_get_class($plugin, 'handler');
  return new $class($entity, $field, $instance);
}

/**
 * Implements hook_field_widget_form().
 */
function pgbar_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  $item = array();
  if (isset($items[$delta])) {
    _pgbar_merge_into($item, $items[$delta]);
    if (isset($item['options']['target']['target']) && is_array($item['options']['target']['target'])) {
      $item['options']['target']['target'] = implode(',', $item['options']['target']['target']);
    }
  }
  if (isset($instance['default_value'][$delta]))
    _pgbar_merge_into($item, $instance['default_value'][$delta]);
  _pgbar_merge_into($item, array(
    'state' => 1,
    'options' => array(
      'target' => array(
        'target'    => '',
        'threshold' => '90',
        'offset'    => '0',
      ),
      'texts' => array(
        'intro_message'       => 'We need !target signatures.',
        'full_intro_message'  => 'Thanks for your support!',
        'status_message'      => 'Already !current of !target signed the petition.',
        'full_status_message' => "We've reached our goal of !target supports.",
      ),
      'display' => array(
        'template' => '',
      ),
    ),
  ));

  $element['state'] = array(
    '#title' => t('Display a progress bar'),
    '#description' => t("If enabled the progressbar is rendered on node display (according to the content-type's display settings"),
    '#type' => 'checkbox',
    '#default_value' => $item['state'],
  );
  $element['options'] = array(
    '#type' => 'vertical_tabs',
    '#title' => t('Progress bar'),
    'target' => array(
      '#type' => 'fieldset',
      '#title' => t('Target value'),
    ),
    'texts' => array(
      '#type' => 'fieldset',
      '#title' => t('Texts'),
    ),
    'source' => array(
      '#type' => 'fieldset',
      '#title' => t('Counter source'),
    ),
    'display' => array(
      '#type' => 'fieldset',
      '#title' => t('Display'),
    ),
    '#states' => array(
        'invisible' => array("#edit-" . strtr($field['field_name'], '_', '-') . "-und-$delta-state" => array('checked' => FALSE)),
    ),
  );
  $element['options']['target']['target'] = array(
    '#title' => t('Goal for this action'),
    '#description' => t('This value will be used as goal in the progress bar. If you add multiple values separated by a comma the progress bar will switch to the next value once current goal is (nearly) reached.'),
    '#type' => 'textfield',
    '#default_value' => $item['options']['target']['target'],
    '#size' => 60,
    '#maxlength' => 60,
  );
  $element['options']['target']['threshold'] = array(
    '#title' => t('Threshold percentage'),
    '#description' => t('Use the smallest step from the above setting that is not yet reached to this percentage.'),
    '#type' => 'textfield',
    '#number_type' => 'integer',
    '#default_value' => $item['options']['target']['threshold'],
  );
  $element['options']['target']['offset'] = array(
    '#title' => t('Collected offline'),
    '#description' => t('Add a constant offset to the number shown by the progress bar.'),
    '#type' => 'textfield',
    '#nimber_type' => 'integer',
    '#default_value' => $item['options']['target']['offset'],
  );
  $element['options']['texts']['intro_message'] = array(
    '#title' => t('Intro message'),
    '#description' => t('This is the message that is displayed above the progress bar.'),
    '#type' => 'textarea',
    '#default_value' => $item['options']['texts']['intro_message'],
    '#rows' => 2,
  );
  $element['options']['texts']['status_message'] = array(
    '#title' => t('Status message'),
    '#description' => t('This is the message that\'s displayed below the progress bar, usually telling the user how much progress has been made already.'),
    '#type' => 'textarea',
    '#rows' => 2,
    '#default_value' => $item['options']['texts']['status_message'],
  );
  $element['options']['texts']['full_intro_message'] = array(
    '#title' => t('Intro message at 100% (or above)'),
    '#description' => t('Intro message when the target is reached (or overreached).'),
    '#type' => 'textarea',
    '#rows' => 2,
    '#default_value' => $item['options']['texts']['full_intro_message'],
  );
  $element['options']['texts']['full_status_message'] = array(
    '#title' => t('Status message at 100% (or above)'),
    '#description' => t('Status message when the target is reached (or overreached).'),
    '#type' => 'textarea',
    '#rows' => 2,
    '#default_value' => $item['options']['texts']['full_status_message'],
  );
  $element['options']['display']['template'] = array(
    '#title' => t('Style'),
    '#description' => t('This field is handed over to the theme engine to enable different progress bar styles.'),
    '#default_value' => $item['options']['display']['template'],
    '#type' => 'textfield',
  );
  $source = _pgbar_source_plugin_load(NULL, $field, $instance);
  if ($source_form = $source->widgetForm(isset($items[$delta]) ? $items[$delta] : NULL)) {
    $element['options']['source'] += $source_form;
  } else {
    $element['options']['source']['#access'] = FALSE;
  }

  $element += array(
    '#type' => 'fieldset',
    '#title' => t('Progress bar'),
  );

  return $element;
}

/**
 * Implements hook_field_formatter_view().
 */
function pgbar_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  module_load_include('inc', 'webform', 'includes/webform.submissions');

  $source = _pgbar_source_plugin_load($entity, $field, $instance);

  $element = array();
  foreach ($items as $delta => $item) {
    $current = $source->getValue($item);
    // Skip disabled items.
    if (!isset($item['state']) || !$item['state']) {
      continue;
    }

    $theme = array();
    if (isset($item['options']['display']['template'])) {
        $theme[] = 'pgbar__' . $item['options']['display']['template'];
    }
    $theme[] = 'pgbar';
    $current += isset($item['options']['target']['offset']) ? $item['options']['target']['offset'] : 0;
    $d = array(
      '#theme' => $theme,
      '#current' => $current,
      '#target' => _pgbar_select_target($item['options']['target']['target'], $current, $item['options']['target']['threshold']),
      '#texts' => $item['options']['texts'],
    );
    // Skip pgbars that have a target of 0
    if ($d['#target'] <= 0) {
      continue;
    }
    $element[] = $d;
  }
  $element['#attached'] = array(
    'js' => array(drupal_get_path('module', 'pgbar') . '/pgbar.js'),
  );
  return count($element) ? $element : NULL;
}

/**
 * Get the first target that is not too close (as defined by percentage).
 * @param $targets array of targets
 * @param $current current value
 * @param $percentage at which to switch to the next target value
 */
function _pgbar_select_target($targets, $current, $percentage) {
  $t = 1;
  while (count($targets)) {
    $t = array_shift($targets);
    if ($current * 100 / $t <= $percentage) {
      return $t;
    }
  }
  return $t;
}

/**
 * Implements hook_field_is_empty().
 */
function pgbar_field_is_empty($item, $field) {
  return FALSE;
}

/**
 * Validation callback for the pgbar field.
 */
function pgbar_number_validate($element, &$form_state, $form) {
  if (!is_numeric($element['#value']) || $element['#value'] == '') {
    form_error($element, t('The field "!name" has to be a number.', array('!name' => t($element['#title']))));
  }
}

/**
 * Implements hook_field_validate().
 */
function pgbar_field_validate($entity_type, $entity, $field, $instance, $langcode, &$items, &$errors) {
}

/**
 * Implements hook_field_presave().
 */
function pgbar_field_presave($entity_type, $entity, $field, $instance, $langcode, &$items) {
  if ($field['type'] == 'pgbar') {
    foreach ($items as &$item) {
      $options = array();
      foreach (array('target', 'texts', 'source', 'display') as $k) {
        if (!isset($item['options'][$k]))
          continue;
        $options[$k] = $item['options'][$k];
      }
      if (!is_array($options['target']['target'])) {
        $targets = array();
        foreach (explode(',', $options['target']['target']) as $n) {
          $targets[] = (int) $n;
        }
        $options['target']['target'] = $targets;
      }
      $item['options'] = serialize($options);
    }
  }
}

/**
 * Implements hook_field_load().
 */
function pgbar_field_load($entity_type, $entities, $field, $instances, $langcode, &$items, $age) {
  if ($field['type'] == 'pgbar') {
    foreach ($entities as $id => $entity) {
      foreach ($items[$id] as &$item) {
        $item['options'] = unserialize($item['options']);
      }
    }
  }
}

/**
 * Implements hook_field_instance_settings_form().
 */
function pgbar_field_instance_settings_form($field, $instance) {
  $settings = &$instance['settings'];

  $form = array();
  ctools_include('plugins');
  $sources = ctools_get_plugins('pgbar', 'source');
  $options = array();
  foreach ($sources as $id => $source) {
    $options[$id] = $source['label'];
  }

  $form['source'] = array(
    '#type' => 'select',
    '#title' => t('Data source'),
    '#description' => 'These plugins decide where the data for the current progress bar value come from',
    '#options' => $options,
    '#default_value' => !empty($settings['source']) ? $settings['source'] : NULL,
  );

  return $form;
}

