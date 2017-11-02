<?php

namespace Drupal\pgbar\Source;

/**
 * @file
 * Implement the webform_field_sum source plugin.
 *
 * Sums up all values submitted for a certain webform component.
 */

class WebformComponentSum implements PluginInterface {
  protected $entity;
  protected $instance;

  /**
   * @var Drupal\pgbar\Source\AddNids
   */
  protected $addNids;

  public static function label() {
    return t('Webform - Sum of a field');
  }

  public static function forField($entity, $field, $instance) {
    return new static($entity, $instance);
  }

  /**
   * Constructor: Save entity and field instance.
   */
  public function __construct($entity, $instance) {
    $this->entity = $entity;
    $this->instance = $instance;
    $this->addNids = new AddNids($entity ? $entity->nid : NULL);
  }

  /**
   * Get the value for the given item.
   *
   * @return int
   *   Sum of all values for the webform_component with
   *   form_key == $item['options']['source']['form_key'] in all
   *   webform submissions in $this-entity and all it's translations.
   */
  public function getValue($item) {
    $form_key = $item['options']['source']['form_key'];
    $q = db_select('webform_submitted_data', 'wsd');
    $q->addExpression('SUM(wsd.data)');
    $q->innerJoin('webform_component', 'wc', 'wsd.nid=wc.nid AND wc.cid=wsd.cid');
    $q->condition('wc.form_key', $form_key);
    $q->condition('wc.nid', $this->addNids->translationsQuery($item), 'IN');
    return $q->execute()->fetchField();
  }

  /**
   * Build the configuration form for the field widget.
   *
   * - Add a field to configure the form_key.
   *   @todo make this a select box instead.
   * - Add a field to include submissions from other nodes.
   */
  public function widgetForm($item) {
    $source_options = isset($item['options']['source']) ? $item['options']['source'] : array();
    $source_options += array('form_key' => '');
    $form['form_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Form key'),
      '#description' => t('All values with this form key are summed up to get the current value for the progress bar.'),
      '#default_value' => $source_options['form_key'],
    );
    $form = $this->addNids->widgetForm($item, $form);
    return $form;
  }

}
