<?php

namespace Drupal\pgbar\Source;

/**
 * @file
 * Define the webform submission source plugin.
 */

class WebformSubmissionCount implements PluginInterface {
  protected $entity;

  public static function label() {
    return t('Webform submission count');
  }

  public static function forField($entity, $field, $instance) {
    return new static($entity);
  }

  /**
   * Constructor: save entity, field and field_instance.
   */
  public function __construct($entity) {
    $this->entity = $entity;
  }
  /**
   * Get the value for the given item.
   *
   * @return int
   *   The number of webform submissions in $this-entity
   *   and all it's translations.
   */
  public function getValue($item) {
    $entity = $this->entity;
    $q = db_select('node', 'n');
    $q->addExpression('COUNT(ws.nid)');
    $q->innerJoin('webform_submissions', 'ws', 'n.nid=ws.nid');
    $q->where(
      'n.nid=:nid OR ((n.nid=:tnid OR n.tnid=:tnid) AND :tnid>0)',
      array(':nid' => $entity->nid, ':tnid' => $entity->tnid)
    );
    return $q->execute()->fetchField();
  }
  /**
   * No extra configuration for the widget needed.
   */
  public function widgetForm($item) {
    return NULL;
  }
}
