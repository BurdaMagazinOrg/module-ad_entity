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
 * Act on the inclusion of Advertising context.
 *
 * @param \Drupal\Core\Field\FieldItemListInterface $items
 *   A list of field items containing Advertising context,
 *   which is about to be included for appliance.
 * @param array $settings
 *   The settings of the formatter being used for the list of field items.
 */
function hook_ad_context_include(\Drupal\Core\Field\FieldItemListInterface $items, array $settings) {
  $to_append['context'] = [
    'context_plugin_id' => 'targeting',
    'apply_on' => [],
    'context_settings' => [
      'targeting' => [
        'targeting' => [
          'entityid' => $items->getEntity()->id(),
        ],
      ],
    ],
  ];
  $items->appendItem($to_append);
}

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
