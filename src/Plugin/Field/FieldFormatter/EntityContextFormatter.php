<?php

namespace Drupal\ad_entity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'ad_entity_context' formatter.
 *
 * @FieldFormatter(
 *   id = "ad_entity_context",
 *   label = @Translation("Advertising context from entity"),
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

    /* TODO apply contexts.
    foreach ($items as $delta => $item) {
    }*/

    return $element;
  }

}
