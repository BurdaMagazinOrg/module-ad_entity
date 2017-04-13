<?php

/**
 * @file
 * Advertising Entity: AdTech Factory theme implementations.
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Crypt;

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
  if (!empty($settings['targeting'])) {
    $variables['targeting'] = $settings['targeting'];
  }

  $variables['attributes'] = $attributes;
}
