<?php

namespace Drupal\ad_entity_dfp\Plugin\ad_entity\AdType;

use Drupal\ad_entity\Entity\AdEntityInterface;
use Drupal\ad_entity\Plugin\AdTypeBase;
use Drupal\ad_entity\TargetingCollection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;

/**
 * Type plugin for DFP advertisement.
 *
 * @AdType(
 *   id = "dfp",
 *   label = "Doubleclick for Publishers (DFP)"
 * )
 */
class DFPType extends AdTypeBase {

  /**
   * {@inheritdoc}
   */
  public function entityConfigForm(array $form, FormStateInterface $form_state, AdEntityInterface $ad_entity) {
    $element = [];

    $settings = $ad_entity->getThirdPartySettings($this->getPluginDefinition()['provider']);

    $element['network_id'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("DFP Network ID"),
      '#default_value' => !empty($settings['network_id']) ? $settings['network_id'] : '',
      '#size' => 15,
      '#required' => TRUE,
    ];

    $element['unit_id'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("DFP Ad Unit ID / Pattern"),
      '#default_value' => !empty($settings['unit_id']) ? $settings['unit_id'] : '',
      '#size' => 15,
      '#required' => TRUE,
    ];

    // Convert sizes settings to user-input format.
    $sizes_default = [];
    if (!empty($settings['sizes'])) {
      $decoded = Json::decode($settings['sizes']);
      foreach ($decoded as $size) {
        $sizes_default[] = $size[0] . 'x' . $size[1];
      }
    }
    $sizes_default = implode(',', $sizes_default);
    $element['sizes'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Ad size formats"),
      '#description' => $this->stringTranslation->translate("Example: <strong>300x600,300x250</strong>. For Out Of Page slots, use 0x0"),
      '#default_value' => $sizes_default,
      '#sizes' => 40,
      '#required' => TRUE,
    ];

    $targeting = !empty($settings['targeting']) ?
      new TargetingCollection($settings['targeting']) : NULL;
    $element['targeting'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Default targeting"),
      '#description' => $this->stringTranslation->translate("Default pairs of key-values for targeting on the ad tag. Example: <strong>pos: top, category: value1, category: value2, ...</strong>"),
      '#default_value' => !empty($targeting) ? $targeting->toUserOutput() : '',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function entityConfigSubmit(array &$form, FormStateInterface $form_state, AdEntityInterface $ad_entity) {
    $provider = $this->getPluginDefinition()['provider'];
    $values = $form_state->getValue(['third_party_settings', $provider]);

    if (!empty($values['targeting'])) {
      // Convert the targeting to a JSON-encoded string.
      $targeting = new TargetingCollection();
      $targeting->collectFromUserInput($values['targeting']);
      $ad_entity->setThirdPartySetting($provider, 'targeting', $targeting->toJson());
    }
    else {
      $ad_entity->setThirdPartySetting($provider, 'targeting', '{}');
    }

    if (!empty($values['sizes'])) {
      // Convert the user-input of sizes to a proper format.
      $size_pairs = explode(',', $values['sizes']);
      $sizes = [];
      foreach ($size_pairs as $pair) {
        $pair = trim($pair);
        $parts = explode('x', $pair);
        $sizes[] = [trim($parts[0]), trim($parts[1])];
      }
      $encoded = str_replace('"', '', Json::encode($sizes));
      $ad_entity->setThirdPartySetting($provider, 'sizes', $encoded);
    }
  }

}
