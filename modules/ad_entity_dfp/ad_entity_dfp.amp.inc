<?php

/**
 * @file
 * Advertising Entity: DFP implementations for Accelerated Mobile Pages (AMP).
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Serialization\Json;
use Drupal\ad_entity\TargetingCollection;

/**
 * Preprocess implementation for a DFP tag for AMP.
 *
 * @param array &$variables
 *   An array of available variables.
 */
function template_preprocess_dfp_amp(array &$variables) {
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  $ad_entity = $variables['ad_entity'];
  $settings = $ad_entity->getThirdPartySettings('ad_entity_dfp');

  $slot_id = '/' . $settings['network_id'] . '/' . $settings['unit_id'];

  // Generate attributes.
  $attributes = new Attribute(['type' => 'doubleclick']);
  $attributes->setAttribute('data-slot', $slot_id);
  $attributes->setAttribute('width', $settings['amp']['width']);
  $attributes->setAttribute('height', $settings['amp']['height']);
  if (!empty($settings['sizes'])) {
    $sizes = [];
    $decoded = Json::decode($settings['sizes']);
    foreach ($decoded as $size) {
      $sizes[] = $size[0] . 'x' . $size[1];
    }
    $sizes = implode(',', $sizes);
    $attributes->setAttribute('data-multi-size', $sizes);
  }
  if (isset($settings['amp']['multi_size_validation'])) {
    if ($settings['amp']['multi_size_validation']) {
      $attributes->setAttribute('data-multi-size-validation', 'true');
    }
    else {
      $attributes->setAttribute('data-multi-size-validation', 'false');
    }
  }
  $variables['attributes'] = $attributes;

  // Generate the json attribute.
  $json = [];
  if (!empty($settings['amp']['same_domain_rendering'])) {
    $json['useSameDomainRenderingUntilDeprecated'] = 1;
  }
  $targeting_collection = $ad_entity->getTargetingFromContextData();
  // Merge the targeting.
  $targeting_collection->collectFromJson($settings['targeting']);
  if (!$targeting_collection->isEmpty()) {
    $json['targeting'] = $targeting_collection->toArray();
  }
  $variables['json'] = $json;
}
