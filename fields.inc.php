<?php

/**
 * Implements hook_field_info()
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
 * Implements hook_field_widget_info()
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
 * Implements hook_field_formatter_info()
 */
function pgbar_field_formatter_info() {
  $info['pgbar'] = array(
    'label' => 'Progress bar',
    'field types' => array('pgbar'),
  );
  return $info;
}

/**
 * Implements hook_field_widget_form()
 */
function pgbar_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  if (isset($items[$delta])) {
    $old = $items[$delta];
  }
  $element['target'] = array(
    '#title' => t('Target value'),
    '#description' => t('This is the value that is taken as 100%.'),
    '#type' => 'textfield',
    '#default_value' => isset($old['target']) ? $old['target'] : '',
    '#size' => 60,
    '#maxlength' => 60,
    '#number_type' => 'integer',
    '#required' => FALSE,
  );
  $element['target']['#element_validate'][] = 'pgbar_number_validate';
  $element['state'] = array(
    '#title' => t('Show this progress bar!'),
    '#description' => t("If enabled the progressbar is rendered on node display (according to the content-type's display settings"),
    '#type' => 'checkbox',
    '#default_value' => isset($old['state']) ? $old['state'] : 0,
  );

  $element += array(
    '#type' => 'fieldset',
  );
  return $element;
}

/**
 * Implements hook_field_formatter_view()
 */
function pgbar_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  module_load_include('inc', 'webform', 'includes/webform.submissions');

  $current = db_query('SELECT COUNT(ws.nid) FROM webform_submissions ws INNER JOIN node n USING (nid) WHERE n.nid=:nid OR n.nid=:tnid OR n.tnid=:tnid', array(':nid' => $entity->nid, ':tnid' => $entity->tnid))->fetchField();

  $element = array();
  foreach ($items as $delta => $item) {
    // skip disabled items
    if (!isset($item['state']) || !$item['state'])
      continue;

    $d = array (
      '#theme' => 'pgbar',
      '#current' => $current,
    );
    foreach ($item as $k => $v) {
      $d['#'.$k] = $v;
    }
    $element[] = $d;
  }
  $element['#attached'] = array(
    'js' => array(drupal_get_path('module', 'pgbar') . '/pgbar.js'),
  );
  return $element;
}

/**
 * Implements hook_field_is_empty()
 */
function pgbar_field_is_empty($item, $field) {
  return empty($item['target']);
}

function pgbar_number_validate($element, &$form_state, $form) {
  if (!is_numeric($element['#value']) || $element['#value'] == '') {
    form_error($element, t('The field "!name" has to be a number.', array('!name' => t($element['#title']))));
  }
}
