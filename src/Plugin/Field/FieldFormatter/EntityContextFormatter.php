<?php

namespace Drupal\ad_entity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'ad_entity_context' formatter.
 *
 * @FieldFormatter(
 *   id = "ad_entity_context",
 *   label = @Translation("Context only from entity content"),
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

    foreach ($items as $delta => $item) {
      $element[$delta] = $this->buildElementFromItem($item);
    }

    return $element;
  }

}
