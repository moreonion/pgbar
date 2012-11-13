<?php
// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:
// kate: tab-indents true; indent-width 4; tab-width 4; mixedindent off; ident-mode cstyle; replace-tabs on;
/**
 * @file
 * Define the pgbar field type.
 */

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
  if (isset($instance['settings']['source'])) {
    $plugin = ctools_get_plugins('pgbar', 'source', $instance['settings']['source']);
    $class = ctools_plugin_get_class($plugin, 'handler');
  } else {
    require_once dirname(__FILE__) . '/plugins/source/webform_submissions.inc.php';
    $class = 'PgbarSourceWebformSubmissions';
  }
  return new $class($entity, $field, $instance);
}

/**
 * Implements hook_field_widget_form().
 */
function pgbar_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  if (isset($items[$delta])) {
    $old = $items[$delta];
  }
  $element['state'] = array(
    '#title' => t('Display a progress bar'),
    '#description' => t("If enabled the progressbar is rendered on node display (according to the content-type's display settings"),
    '#type' => 'checkbox',
    '#default_value' => isset($old['state']) ? $old['state'] : 1,
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
    '#states' => array(
      'invisible' => array("#edit-field-petition-pgbar-und-$delta-state" => array('checked' => FALSE)),
    ),
  );
  $element['options']['target']['target'] = array(
    '#title' => t('Target value steps (comma separated)'),
    '#description' => t('The target value for the progress bar is automatically increased using these steps.'),
    '#type' => 'textfield',
    '#default_value' => isset($old['options']['target']['target']) ? implode(',', $old['options']['target']['target']) : '',
    '#size' => 60,
    '#maxlength' => 60,
  );
  $element['options']['target']['threshold'] = array(
    '#title' => t('Threshold percentage'),
    '#description' => t('Use the smallest step from the above setting that is not yet reached to this percentage.'),
    '#type' => 'textfield',
    '#number_type' => 'integer',
    '#default_value' => isset($old['options']['target']['threshold']) ? $old['options']['target']['threshold'] : '90',
  );
  $element['options']['target']['offset'] = array(
    '#title' => t('Collected offline'),
    '#description' => t('Add a constant offset to the number shown by the progress bar.'),
    '#type' => 'textfield',
    '#nimber_type' => 'integer',
    '#default_value' => isset($old['options']['target']['offset']) ? $old['options']['target']['offset'] : 0,
  );
  $element['options']['texts']['intro_message'] = array(
    '#title' => t('Intro message'),
    '#description' => t('This is the message that is displayed above the progress bar.'),
    '#type' => 'textarea',
    '#default_value' => isset($old['options']['texts']['intro_message']) ? $old['options']['texts']['intro_message'] : 'We need !target signatures.',
    '#rows' => 2,
  );
  $element['options']['texts']['status_message'] = array(
    '#title' => t('Status message'),
    '#description' => t('This is the message that\'s displayed below the progress bar, usually telling the user how much progress has been made already.'),
    '#type' => 'textarea',
    '#rows' => 2,
    '#default_value' => isset($old['options']['texts']['status_message']) ? $old['options']['texts']['status_message'] : 'Already !current of !target signed the petition.',
  );
  $element['options']['texts']['full_intro_messages'] = array(
    '#title' => t('Intro message at 100% (or above)'),
    '#description' => t('Intro message when the target is reached (or overreached).'),
    '#type' => 'textarea',
    '#rows' => 2,
    '#default_value' => isset($old['options']['texts']['full_intro_messages']) ? $old['options']['texts']['full_intro_messages'] : "Thanks for your support!",
  );
  $element['options']['texts']['full_status_messages'] = array(
    '#title' => t('Status message at 100% (or above)'),
    '#description' => t('Status message when the target is reached (or overreached).'),
    '#type' => 'textarea',
    '#rows' => 2,
    '#default_value' => isset($old['options']['texts']['full_status_messages']) ? $old['options']['texts']['full_status_messages'] : "We've reached our goal of !target supports.",
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
    if ($entity instanceof Entity) {
      $theme[] = 'pgbar__' . $entity_type . '__' . $entity->bundle();
    } elseif ($entity_type == 'node') {
      $theme[] = 'pgbar__' . $entity_type . '__' . $entity->type;
    }
    $theme[] = 'pgbar__' . $entity_type;
    $theme[] = 'pgbar';
    $current += isset($item['options']['target']['offset']) ? $item['options']['target']['offset'] : 0;
    $d = array(
      '#theme' => $theme,
      '#current' => $current,
      '#target' => _pgbar_select_target($item['options']['target']['target'], $current, $item['options']['target']['threshold']),
      '#texts' => $item['options']['texts'],
    );
    $element[] = $d;
  }
  $element['#attached'] = array(
    'js' => array(drupal_get_path('module', 'pgbar') . '/pgbar.js'),
  );
  return $element;
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
  return empty($item['options']['target']['target']);
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
      foreach (array('target', 'texts', 'source') as $k) {
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
  $form['source'] = array(
    '#type' => 'select',
    '#title' => t('Data source'),
    '#description' => 'These plugins decide where the data for the current progress bar value come from',
  );

  $sources = ctools_get_plugins('pgbar', 'source');
  $options = array();
  foreach ($sources as $id => $source) {
    $options[$id] = $source['label'];
  }
  $form['source']['#options'] = $options;

  return $form;
}

function pgbar_ctools_plugin_directory($module, $plugin) {
  if ($module == 'pgbar' && $plugin == 'source') {
    return 'plugins/source';
  }
}
