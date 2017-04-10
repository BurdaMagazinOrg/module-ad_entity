<?php

namespace Drupal\ad_entity_adtech\Plugin\ad_entity\AdType;

use Drupal\ad_entity\Plugin\AdTypeBase;
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

    $settings = $config->get('adtech_factory');

    $element['library_source'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Library source"),
      '#description' => $this->stringTranslation->translate("The source of the external AdTech Library, which will be embedded inside the HTML head."),
      '#default_value' => !empty($settings['library_source']) ? $settings['library_source'] : '',
      '#field_prefix' => 'src="',
      '#field_suffix' => '"',
    ];

    $element['page_targeting'] = [
      '#type' => 'textfield',
      '#title' => $this->stringTranslation->translate("Default page targeting"),
      '#description' => $this->stringTranslation->translate("Default pairs of key-values for targeting on the page. Format: <strong>pos: 'top', category: ['value1', 'value2']</strong>, ..."),
      '#default_value' => !empty($settings['page_targeting']) ? $settings['page_targeting'] : '',
    ];

    return $element;
  }

}
