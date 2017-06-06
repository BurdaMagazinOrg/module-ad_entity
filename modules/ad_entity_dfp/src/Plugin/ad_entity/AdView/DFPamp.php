<?php

namespace Drupal\ad_entity_dfp\Plugin\ad_entity\AdView;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ad_entity\Entity\AdEntityInterface;
use Drupal\ad_entity\Plugin\AdViewBase;

/**
 * View handler plugin for DFP advertisement as AMP (Accelerated Mobile Pages).
 *
 * @AdView(
 *   id = "dfp_amp",
 *   label = "DFP tag for Accelerated Mobile Pages",
 *   allowedTypes = {
 *     "dfp"
 *   }
 * )
 */
class DFPamp extends AdViewBase {

  /**
   * {@inheritdoc}
   */
  public function build(AdEntityInterface $entity) {
    return [
      '#theme' => 'dfp_amp',
      '#ad_entity' => $entity,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function entityConfigForm(array $form, FormStateInterface $form_state, AdEntityInterface $ad_entity) {
    $element = [];

    $settings = $ad_entity->getThirdPartySettings($this->getPluginDefinition()['provider']);

    $element['amp']['width'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->stringTranslation->translate('AMP-AD tag width'),
      '#size' => 10,
      '#field_prefix' => 'width="',
      '#field_suffix' => '"',
      '#default_value' => !empty($settings['amp']['width']) ? $settings['amp']['width'] : '',
    ];

    $element['amp']['height'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->stringTranslation->translate('AMP-AD tag height'),
      '#size' => 10,
      '#field_prefix' => 'height="',
      '#field_suffix' => '"',
      '#default_value' => !empty($settings['amp']['height']) ? $settings['amp']['height'] : '',
    ];

    $element['amp']['multi_size_validation'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => $this->stringTranslation->translate('Multi-size validation'),
      '#options' => ['1' => 'true', '0' => 'false'],
      '#default_value' => !empty($settings['amp']['multi_size_validation']) ? $settings['amp']['multi_size_validation'] : 0,
    ];

    return $element;
  }

}
