<?php

$plugin = array(
  'label' => t('Webform Submissions'),
  'handler' => array('class' => 'PgbarSourceWebformSubmissions'),
);

class PgbarSourceWebformSubmissions {
  public function __construct($entity, $field, $instance) {
    $this->entity = $entity;
    $this->field = $field;
    $this->instance = $instance;
  }
  public function getValue($item) {
    $entity = $this->entity;
    if ($entity) {
      return db_query('SELECT COUNT(ws.nid) FROM webform_submissions ws INNER JOIN node n USING (nid) WHERE n.nid=:nid OR ((n.nid=:tnid OR n.tnid=:tnid) AND :tnid>0)', array(':nid' => $entity->nid, ':tnid' => $entity->tnid))->fetchField();
    } else {
      return 0;
    }
  }
  public function widgetForm($item) {
    return NULL;
  }
}
