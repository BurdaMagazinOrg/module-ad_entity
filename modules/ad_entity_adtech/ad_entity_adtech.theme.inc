<?php

/**
 * @file
 * Advertising Entity: AdTech Factory theme implementations.
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Crypt;

function template_preprocess_adtech_factory_default(&$variables) {
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  $ad_entity = $variables['ad_entity'];
  $settings = $ad_entity->getThirdPartySettings('ad_entity_adtech');

  $id = 'atf-' . Crypt::randomBytes(4);
  $attributes = new Attribute(['id' => $id]);
  $attributes->addClass("adtech-factory-default");

  if (!empty($settings['data_atf'])) {
    $attributes->setAttribute('data-atf', $settings['data_atf']);
  }
  if (!empty($settings['data_atf_format'])) {
    $attributes->setAttribute('data-atf-format', $settings['data_atf_format']);
  }

  $variables['attributes'] = $attributes;
}
