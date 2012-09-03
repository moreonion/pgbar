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

  $current = db_query('SELECT COUNT(ws.nid) FROM webform_submissions ws INNER JOIN node n USING (nid) WHERE n.nid=:nid OR n.nid=:tnid OR n.tnid=:tnid', array(':nid' => $entity->nid, ':tnid' => $entity->tnid))->fetchField();

  $element = array();
  foreach ($items as $delta => $item) {
    // Skip disabled items.
    if (!isset($item['state']) || !$item['state']) {
      continue;
    }

    $d = array(
      '#theme' => 'pgbar',
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
      foreach (array('target', 'texts') as $k) {
        $options[$k] = $item['options'][$k];
      }
      $targets = array();
      foreach (explode(',', $options['target']['target']) as $n) {
        $targets[] = (int) $n;
      }
      $options['target']['target'] = $targets;
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
