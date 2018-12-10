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
 *   container = "amp",
 *   allowedTypes = {
 *     "dfp"
 *   }
 * )
 */
class DFPAmp extends AdViewBase {

  /**
   * The blocking behavior options.
   *
   * @var array
   */
  static protected $blockOnConsentOptions = [
    '0' => 'Not enabled',
    '_till_accepted' => 'Enabled until accepted (default behavior)',
    '_till_responded' => 'Enabled until responded',
    '_auto_reject' => 'Auto reject',
  ];

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
      '#title' => $this->stringTranslation->translate('Enable multi-size validation'),
      '#description' => $this->stringTranslation->translate('Read more about this <a href="@url" target="_blank" rel="noopener noreferrer">here</a>.', ['@url' => 'https://github.com/ampproject/amphtml/blob/master/ads/google/doubleclick.md#multi-size-ad']),
      '#options' => ['1' => $this->stringTranslation->translate('yes'), '0' => $this->stringTranslation->translate('no')],
      '#default_value' => !empty($settings['amp']['multi_size_validation']) ? $settings['amp']['multi_size_validation'] : 0,
    ];

    $element['amp']['same_domain_rendering'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => $this->stringTranslation->translate('Enable same domain rendering'),
      '#description' => $this->stringTranslation->translate('Read more about this <a href="@url" target="_blank" rel="noopener noreferrer">here</a>.', ['@url' => 'https://github.com/ampproject/amphtml/blob/master/ads/google/doubleclick.md#temporary-use-of-usesamedomainrenderinguntildeprecated']),
      '#options' => ['1' => $this->stringTranslation->translate('yes'), '0' => $this->stringTranslation->translate('no')],
      '#default_value' => !empty($settings['amp']['same_domain_rendering']) ? $settings['amp']['same_domain_rendering'] : 0,
    ];

    $element['amp']['consent'] = [
      '#type' => 'fieldset',
      '#title' => $this->stringTranslation->translate('Personalization by consent'),
      '#description' => $this->stringTranslation->translate('Read more about this <a href="@url" target="_blank" rel="noopener noreferrer">here</a>.', ['@url' => 'https://support.google.com/admanager/answer/7678538']),
    ];
    $block_on_consent_options = static::blockOnConsentOptions();
    foreach ($block_on_consent_options as &$value) {
      $value = $this->stringTranslation->translate($value);
    }
    $element['amp']['consent']['block_behavior'] = [
      '#type' => 'select',
      '#title' => $this->stringTranslation->translate('Blocking behavior'),
      '#options' => $block_on_consent_options,
      '#default_value' => !empty($settings['amp']['consent']['block_behavior']) ? $settings['amp']['consent']['block_behavior'] : '0',
      '#empty_value' => '0',
    ];
    $element['amp']['consent']['npa_unknown'] = [
      '#type' => 'checkbox',
      '#title' => $this->stringTranslation->translate('Request non-personalized ads when consent is unknown.'),
      '#default_value' => !empty($settings['amp']['consent']['npa_unknown']),
    ];

    return $element;
  }

  /**
   * Get allowed blocking behavior options.
   *
   * @return array
   *   The blocking behavior options.
   */
  public static function blockOnConsentOptions() {
    return static::$blockOnConsentOptions;
  }

}
