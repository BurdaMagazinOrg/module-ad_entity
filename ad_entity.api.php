<?php

/**
 * @file
 * Hooks for ad_entity module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Act on resetting the backend context data for the given entity.
 *
 * For more information, see AdContextManager::resetContextDataForEntity().
 *
 * @param \Drupal\ad_entity\Plugin\AdContextManager $context_manager
 *   The manager for Advertising context plugins and backend context data.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity for which the context data has been reset.
 */
function hook_ad_context_data_reset(\Drupal\ad_entity\Plugin\AdContextManager $context_manager, \Drupal\Core\Entity\EntityInterface $entity) {
  $context_manager
    ->addContextData('targeting', ['targeting' => ['key' => 'value']]);
}

/**
 * @} End of "addtogroup hooks".
 */
