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
 * @param array &$variables
 *   An array of variables.
 */
function template_preprocess_ad_entity(array &$variables) {
  /** @var \Drupal\ad_entity\Entity\AdEntityInterface $ad_entity */
  $ad_entity = $variables['ad_entity'];

  // Generate attributes.
  $id = 'ad-entity-' . Crypt::randomBytesBase64(8);
  $attributes = new Attribute(['id' => $id]);
  $attributes->addClass('ad-entity-container');
  $attributes->setAttribute('data-ad-entity', $ad_entity->id());
  $attributes->setAttribute('data-ad-entity-device', $variables['device']);
  $attributes->setAttribute('data-ad-entity-type', $ad_entity->get('type_plugin_id'));
  $attributes->setAttribute('data-ad-entity-view', $ad_entity->get('view_plugin_id'));
  $variables['attributes'] = $attributes;

  // Attach the fundamental JS implementation for viewing Advertising entities.
  $variables['#attached']['library'][] = 'ad_entity/view';

  if ($view_handler = $ad_entity->getViewPlugin()) {
    $definition = $view_handler->getPluginDefinition();
    // Let the assigned view handler build the content.
    $variables['content'] = $view_handler->build($ad_entity);
    // Attach the JS implementation for the view handler.
    $variables['#attached']['library'][] = $definition['library'];
  }
}

/**
 * Preprocess implementation for delivering Advertising context.
 *
 * @param array &$variables
 *   An array of variables.
 */
function template_preprocess_ad_entity_context(array &$variables) {
  $item = $variables['item'];
  $definition = $variables['definition'];
  $id = $definition['id'];
  $context_settings = $item->get('context_settings')->getValue();
  $apply_on = $item->get('apply_on')->getValue();

  $variables['context_id'] = $id;
  // Build the context object.
  $variables['context'] = ['context_id' => $id];
  if (!empty($apply_on)) {
    $variables['context']['apply_on'] = $apply_on;
  }
  if (!empty($context_settings[$id])) {
    $variables['context']['settings'] = $context_settings[$id];
  }

  // Attach the fundamental JS implementation for applying Advertising context.
  $variables['#attached']['library'][] = 'ad_entity/context';
  // Attach the JS implementation for the context plugin.
  $variables['#attached']['library'][] = $definition['library'];
}
