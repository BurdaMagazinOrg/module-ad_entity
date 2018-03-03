<?php

/**
 * @file
 * Advertising Entity: AdTech Factory iFrame implementations.
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Crypt;

/**
 * Preprocess implementation for an AdTech Factory tag as iFrame.
 *
 * @param array &$variables
 *   An array of available variables.
 */
function template_preprocess_adtech_iframe(array &$variables) {
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  $ad_entity = $variables['ad_entity'];
  $settings = $ad_entity->getThirdPartySettings('ad_entity_adtech');

  // Generate attributes.
  $id = 'adtech-' . Crypt::randomBytesBase64(8);
  $attributes = new Attribute(['id' => $id]);
  $attributes->addClass('adtech-factory-ad');
  $attributes->addClass('adtech-iframe-view');
  if (!empty($settings['data_atf'])) {
    $attributes->setAttribute('data-atf', $settings['data_atf']);
  }
  if (!empty($settings['data_atf_format'])) {
    $attributes->setAttribute('data-atf-format', $settings['data_atf_format']);
  }
  $variables['attributes'] = $attributes;

  if (!empty($settings['iframe'])) {
    $variables['iframe'] = $settings['iframe'];
  }

  // Insert targeting from backend context data.
  $targeting_collection = $ad_entity->getTargetingFromContextData();
  if (!$targeting_collection->isEmpty()) {
    $variables['targeting'] = $targeting_collection->toJson();
  }
  else {
    $variables['targeting'] = '{}';
  }

  if ($config = \Drupal::config('ad_entity.settings')) {
    $global_settings = $config->get('adtech_factory');
    if (!empty($global_settings['library_source'])) {
      $variables['library_source'] = $global_settings['library_source'];
    }
    $variables['page_targeting'] = !empty($global_settings['page_targeting']) ?
      $global_settings['page_targeting'] : '{}';
  }
}
