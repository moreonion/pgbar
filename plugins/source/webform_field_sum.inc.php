<?php

$plugin = array(
  'label' => t('Webform - Sum of a field'),
  'handler' => array('class' => 'PgbarSourceWebformSum'),
);

class PgbarSourceWebformSum {
  public function __construct($entity, $instance) {
    $this->entity = $entity;
    $this->instance = $instance;
  }
  public function getValue($item) {
    $entity = $this->entity;
    $sql = 'SELECT sum(wsd.data)'
         . ' FROM webform_submitted_data wsd'
         . ' INNER JOIN webform_component wc ON wsd.nid=wc.nid AND wc.cid=wsd.cid'
         . ' INNER JOIN node n ON wc.nid=n.nid'
         . ' WHERE wc.form_key=:fkey AND (n.nid=:nid OR n.nid=:tnid OR n.tnid=:tnid)';
    return db_query($sql, array(':nid' => $entity->nid, ':tnid' => $entity->tnid, ':fkey' => $item['options']['source']['form_key']))->fetchField();
  }
  public function widgetForm($item) {
    $source_options = isset($item['options']['source']) ? $item['options']['source'] : array();
    $source_options += array('form_key' => '');
    $form['form_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Form key'),
      '#desription' => t('All values with this form key are summed up to get the current value for the progress bar.'),
      '#default_value' => $source_options['form_key'],
    );
    return $form;
  }
}
