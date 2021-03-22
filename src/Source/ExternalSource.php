<?php

namespace Drupal\pgbar\Source;

/**
 * Provides widget and functionality to add an external source.
 *
 * The external source overrides the configured source.
 */
class ExternalSource {

  /**
   * Build the configuration form widget.
   *
   * @param array $item
   *   A field item.
   * @param array $form
   *   A Form-API array to extend.
   *
   * @return array
   *   Form-API array.
   */
  public function widgetForm(array $item, array $form = []) {
    $item['options']['source'] += [
      'enable_external_url' => 0,
      'external_url' => '',
      'find_at' => '',
    ];
    $source_options = $item['options']['source'];
    $form['enable_external_url'] = [
      '#title' => t('Enable an external URL'),
      '#description' => t('This overrides the configured source.'),
      '#type' => 'checkbox',
      '#default_value' => $source_options['enable_external_url'],
    ];
    $form['external_url'] = [
      '#title' => t('External URL'),
      '#description' => t('Enter an external URL to poll.'),
      '#type' => 'textfield',
      '#default_value' => $source_options['external_url'],
    ];
    return $form;
  }

}
