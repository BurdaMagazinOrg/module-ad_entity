<?php

/**
 * @file
 * Advertising Entity: DFP theme implementations.
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Crypt;

/**
 * Preprocess implementation for a default DFP tag view.
 *
 * @param array &$variables
 *   An array of available variables.
 */
function template_preprocess_dfp_default(array &$variables) {
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  $ad_entity = $variables['ad_entity'];
  $settings = $ad_entity->getThirdPartySettings('ad_entity_dfp');

  // Generate attributes.
  $id = 'google-dfp-' . Crypt::randomBytesBase64(8);
  $attributes = new Attribute(['id' => $id]);
  $attributes->addClass('google-dfp-ad');
  $attributes->addClass('dfp-default-view');
  $attributes->setAttribute('data-dfp-network', $settings['network_id']);
  $attributes->setAttribute('data-dfp-unit', $settings['unit_id']);
  $variables['attributes'] = $attributes;

  if (!empty($settings['sizes'])) {
    $variables['sizes'] = $settings['sizes'];
  }
}
