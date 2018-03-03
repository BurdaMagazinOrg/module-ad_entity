<?php

namespace Drupal\ad_entity_dfp\Plugin\ad_entity\AdType;

use Drupal\ad_entity\Entity\AdEntityInterface;
use Drupal\ad_entity\Plugin\AdTypeBase;
use Drupal\ad_entity\Plugin\ad_entity\AdContext\TargetingContext;
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

    $context = !empty($settings['targeting']) ?
      TargetingContext::getJsonDecode($settings['targeting']) : [];
    $targeting = isset($context['targeting']) ?
      new TargetingCollection($context['targeting']) : NULL;
    $element['targeting'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Default targeting"),
      '#description' => $this->stringTranslation->translate("Default pairs of key-values for targeting on the ad tag. Example: <strong>pos: top, category: value1, category: value2, ...</strong>"),
      '#default_value' => isset($targeting) ? $targeting->toUserOutput() : '',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function entityConfigSubmit(array &$form, FormStateInterface $form_state, AdEntityInterface $ad_entity) {
    $provider = $this->getPluginDefinition()['provider'];
    $values = $form_state->getValue(['third_party_settings', $provider]);

    $targeting_empty = TRUE;
    $targeting_value = trim($values['targeting']);
    if (!empty($targeting_value)) {
      // Serialize the default targeting as context data.
      $targeting = new TargetingCollection();
      $targeting->collectFromUserInput($targeting_value);
      if (!$targeting->isEmpty()) {
        $context_data = TargetingContext::getJsonEncode(['targeting' => $targeting->toArray()]);
        $ad_entity->setThirdPartySetting($provider, 'targeting', $context_data);
        $targeting_empty = FALSE;
      }
    }
    if ($targeting_empty) {
      $ad_entity->setThirdPartySetting($provider, 'targeting', NULL);
    }

    if (!empty($values['sizes'])) {
      // Convert the user-input of sizes to a proper format.
      $size_pairs = explode(',', $values['sizes']);
      $sizes = [];
      foreach ($size_pairs as $pair) {
        $pair = trim($pair);
        $parts = explode('x', $pair);
        $sizes[] = [(int) $parts[0], (int) $parts[1]];
      }
      $encoded = str_replace('"', '', Json::encode($sizes));
      $ad_entity->setThirdPartySetting($provider, 'sizes', $encoded);
    }
  }

}
