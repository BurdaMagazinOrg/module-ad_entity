<?php

namespace Drupal\ad_entity;

use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Helper functions for working with Advertising entities.
 */
abstract class Helpers {

  /**
   * Collects backend context data provided by the fields of the given entity.
   *
   * Any data found will be added to the collection
   * managed by the AdContextManager.
   * The data will be fetched from the Advertising context fields.
   * If and how Advertising context is being delivered, depends on the
   * (already configured) display options of the entity's fields.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity from which to fetch context data.
   * @param string|array $display_options
   *   (Optional) Can be either the name of a view mode which has properly
   *   configured field formatters for the Advertising context fields,
   *   or an array of display settings.
   *   See EntityViewBuilderInterface::viewField() for more information.
   */
  public static function collectContextDataFrom(FieldableEntityInterface $entity, $display_options = 'default') {
    $context_fields = [];
    foreach ($entity->getFieldDefinitions() as $definition) {
      if ($definition->getType() == 'ad_entity_context') {
        $context_fields[$definition->getName()] = $definition;
      }
    }

    $formatter_manager = \Drupal::service('plugin.manager.field.formatter');
    if (is_string($display_options)) {
      // Fetch the configured display options for this view mode.
      $display_storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');
      /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
      $display = $display_storage->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $display_options);
      foreach ($context_fields as $field_name => $definition) {
        if ($configured_options = $display->getComponent($field_name)) {
          $configured_options['settings']['appliance_mode'] = 'backend';
          $configured_options['field_definition'] = $definition;
          $configured_options['view_mode'] = $display_options;
          /** @var \Drupal\Core\Field\FormatterInterface $formatter */
          $formatter = $formatter_manager
            ->createInstance($configured_options['type'], $configured_options);
          $formatter->viewElements($entity->get($field_name), $entity->language()->getId());
        }
      }
    }
    else {
      if (empty($display_options['settings']['appliance_mode'])) {
        $display_options['settings']['appliance_mode'] = 'backend';
      }
      foreach ($context_fields as $field_name => $definition) {
        /** @var \Drupal\Core\Field\FormatterInterface $formatter */
        $formatter = $formatter_manager
          ->createInstance($display_options['type'], $display_options);
        $formatter->viewElements($entity->get($field_name), $entity->language()->getId());
      }
    }
  }

}
