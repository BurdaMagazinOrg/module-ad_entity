<?php

/**
 * @file
 * Advertising Entity theme implementations.
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Crypt;

/**
 * Preprocess implementation for viewing an Advertising entity.
 *
 * @param array $variables
 *   An array of variables.
 */
function template_preprocess_ad_entity(array &$variables) {
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  $ad_entity = $variables['ad_entity'];
  // Let the assigned view handler build the content.
  if ($view_handler = $ad_entity->getViewPlugin()) {
    $variables['content'] = $view_handler->build($ad_entity);
  }

  // Generate attributes.
  $id = 'ad-entity-' . Crypt::randomBytesBase64(8);
  $attributes = new Attribute(['id' => $id]);
  $attributes->addClass('ad-entity-container');
  $attributes->setAttribute('data-ad-entity', $ad_entity->id());
  $attributes->setAttribute('data-ad-entity-device', $variables['device']);
  $attributes->setAttribute('data-ad-entity-type', $ad_entity->get('type_plugin_id'));
  $attributes->setAttribute('data-ad-entity-view', $ad_entity->get('view_plugin_id'));
  $variables['attributes'] = $attributes;

  // Attach the corresponding library.
  $variables['#attached']['library'][] =  'ad_entity/view';
}
