<?php

namespace Drupal\ad_entity\Plugin\ad_entity\AdContext;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ad_entity\Plugin\AdContextBase;
use Drupal\ad_entity\TargetingCollection;

/**
 * Targeting context plugin.
 *
 * @AdContext(
 *   id = "targeting",
 *   label = "Targeting",
 *   library = "ad_entity/targeting_context"
 * )
 */
class TargetingContext extends AdContextBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $settings, array $form, FormStateInterface $form_state) {
    $element = [];

    $targeting = !empty($settings['targeting']) ?
      new TargetingCollection($settings['targeting']) : NULL;
    $element['targeting'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Targeting"),
      '#description' => $this->stringTranslation->translate("Pairs of key-values. Example: <strong>pos: top, category: value1, category: value2, ...</strong>"),
      '#default_value' => !empty($targeting) ? $targeting->toUserOutput() : '',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageSettings(array $settings) {
    if (!empty($settings['targeting'])) {
      $targeting = new TargetingCollection();
      $targeting->collectFromUserInput($settings['targeting']);
      $settings['targeting'] = $targeting->toArray();
    }
    return parent::massageSettings($settings);
  }

}
