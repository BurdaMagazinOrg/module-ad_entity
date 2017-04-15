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

    // @todo Display the contexts, either via drupalSettings
    // or via DOM application/json script tags.
    // No need to create instances of context plugins,
    // because the definition already contains
    // the only needed JS library to use.

    return $element;
  }

}
