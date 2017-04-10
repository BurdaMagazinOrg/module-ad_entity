<?php

namespace Drupal\ad_entity_adtech\Plugin\ad_entity\AdType;

use Drupal\ad_entity\Entity\AdEntityInterface;
use Drupal\ad_entity\Plugin\AdTypeBase;
use Drupal\ad_entity_adtech\TargetingCollection;
use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormStateInterface;

/**
 * Type plugin for AdTech Factory advertisement.
 *
 * @AdType(
 *   id = "adtech_factory",
 *   label = "AdTech Factory"
 * )
 */
class AdTechType extends AdTypeBase {

  /**
   * {@inheritdoc}
   */
  public function globalSettingsForm(array $form, FormStateInterface $form_state, Config $config) {
    $element = [];

    $settings = $config->get($this->getPluginDefinition()['id']);

    $element['library_source'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Library source"),
      '#description' => $this->stringTranslation->translate("The source of the external AdTech Library, which will be embedded inside the HTML head."),
      '#default_value' => !empty($settings['library_source']) ? $settings['library_source'] : '',
      '#field_prefix' => 'src="',
      '#field_suffix' => '"',
    ];

    // TODO De-serialize JSON with new TargetingCollection($settings['page_targeting']).
    $element['page_targeting'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Default page targeting"),
      '#description' => $this->stringTranslation->translate("Default pairs of key-values for targeting on the page. Example: <strong>pos: top, category: value1, category: value2, ...</strong>"),
      '#default_value' => !empty($settings['page_targeting']) ? $settings['page_targeting'] : '',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function globalSettingsSubmit(array &$form, FormStateInterface $form_state, Config $config) {
    $id = $this->getPluginDefinition()['id'];
    $values = $form_state->getValue($id);

    if (!empty($values['page_targeting'])) {
      // Convert the targeting to a JSON-formatted string.
      $collection = new TargetingCollection();
      $collection->collectFromUserInput($values['page_targeting']);
      $config->set($id . '.page_targeting', $collection->toJson());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function entityConfigForm(array $form, FormStateInterface $form_state, AdEntityInterface $ad_entity) {
    $element = [];

    $settings = $ad_entity->getThirdPartySettings($this->getPluginDefinition()['provider']);

    $element['data_atf'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Value for the data-atf attribute on the ad tag"),
      '#default_value' => !empty($settings['data_atf']) ? $settings['data_atf'] : 'tag',
      '#field_prefix' => 'data-atf="',
      '#field_suffix' => '"',
      '#size' => 10,
      '#required' => TRUE,
    ];

    $element['data_atf_format'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Value for the data-atf-format attribute on the ad tag"),
      '#default_value' => !empty($settings['data_atf_format']) ? $settings['data_atf_format'] : '',
      '#description' => $this->stringTranslation->translate("Examples: <strong>leaderboard, skyscraper, special</strong>"),
      '#field_prefix' => 'data-atf-format="',
      '#field_suffix' => '"',
      '#size' => 30,
      '#required' => TRUE,
    ];

    $element['targeting'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Default targeting"),
      '#description' => $this->stringTranslation->translate("Default pairs of key-values for targeting on the ad tag. Example: <strong>pos: top, category: value1, category: value2, ...</strong>"),
      '#default_value' => !empty($settings['targeting']) ? $settings['targeting'] : '',
    ];

    return $element;
  }

}
