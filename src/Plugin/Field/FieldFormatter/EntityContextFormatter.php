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

    foreach ($items as $delta => $item) {
      if ($context_item = $item->get('context')) {
        $id = $context_item->get('context_plugin_id')->getValue();
        if ($id && $this->contextManager->hasDefinition($id)) {
          $element[$delta] = [
            '#theme' => 'ad_entity_context',
            '#item' => $context_item,
            '#definition' => $this->contextManager->getDefinition($id),
          ];
        }
      }
    }

    return $element;
  }

}
