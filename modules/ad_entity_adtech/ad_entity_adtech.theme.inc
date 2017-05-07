<?php

/**
 * @file
 * Advertising Entity: AdTech Factory theme implementations.
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Crypt;
use Drupal\ad_entity\TargetingCollection;

/**
 * Preprocess implementation for a default AdTech Factory tag view.
 *
 * @param array &$variables
 *   An array of available variables.
 */
function template_preprocess_adtech_default(array &$variables) {
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  $ad_entity = $variables['ad_entity'];
  $settings = $ad_entity->getThirdPartySettings('ad_entity_adtech');

  // Generate attributes.
  $id = 'adtech-' . Crypt::randomBytesBase64(8);
  $attributes = new Attribute(['id' => $id]);
  $attributes->addClass('adtech-factory-ad');
  $attributes->addClass('adtech-default-view');
  if (!empty($settings['data_atf'])) {
    $attributes->setAttribute('data-atf', $settings['data_atf']);
  }
  if (!empty($settings['data_atf_format'])) {
    $attributes->setAttribute('data-atf-format', $settings['data_atf_format']);
  }
  $targeting_collection = $ad_entity->getTargetingFromContextData();
  if (!empty($settings['targeting']) && $targeting_collection->isEmpty()) {
    $variables['targeting'] = $settings['targeting'];
  }
  elseif (!empty($settings['targeting']) && !$targeting_collection->isEmpty()) {
    // Merge the targeting.
    $targeting_collection->collectFromJson($settings['targeting']);
    $variables['targeting'] = $targeting_collection->toJson();
  }
  else {
    $variables['targeting'] = '{}';
  }
  $variables['attributes'] = $attributes;
}
