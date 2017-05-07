<?php

namespace Drupal\ad_entity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'ad_entity_context' formatter.
 *
 * @FieldFormatter(
 *   id = "ad_entity_context",
 *   label = @Translation("Context from entity only"),
 *   field_types = {
 *     "ad_entity_context"
 *   }
 * )
 */
class EntityContextFormatter extends ContextFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $appliance_mode = $this->getSetting('appliance_mode');

    if ($appliance_mode == 'frontend' || $appliance_mode == 'both') {
      foreach ($items as $item) {
        $element[] = $this->buildElementFromItem($item);
      }
    }

    if ($appliance_mode == 'backend' || $appliance_mode == 'both') {
      foreach ($items as $item) {
        $this->addItemToContextData($item);
      }
    }

    return $element;
  }

}
