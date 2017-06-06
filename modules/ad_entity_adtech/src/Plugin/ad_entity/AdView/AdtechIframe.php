<?php

namespace Drupal\ad_entity_adtech\Plugin\ad_entity\AdView;

use Drupal\ad_entity\Entity\AdEntityInterface;
use Drupal\ad_entity\Plugin\AdViewBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * View handler plugin for AdTech Factory advertisement as iFrames.
 *
 * @AdView(
 *   id = "adtech_iframe",
 *   label = "AdTech Factory tag as iFrame",
 *   allowedTypes = {
 *     "adtech_factory"
 *   }
 * )
 */
class AdtechIframe extends AdViewBase {

  /**
   * {@inheritdoc}
   */
  public function build(AdEntityInterface $entity) {
    return [
      '#theme' => 'adtech_iframe',
      '#ad_entity' => $entity,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function entityConfigForm(array $form, FormStateInterface $form_state, AdEntityInterface $ad_entity) {
    $element = [];

    $settings = $ad_entity->getThirdPartySettings($this->getPluginDefinition()['provider']);

    $element['targeting_hint'] = [
      '#markup' => $this->stringTranslation->translate("For Facebook Instant Articles, make sure the <strong>default targeting</strong> above contains <strong>website: Your Website name</strong> and <strong>channel: FIA</strong> in case you have no other channel specified."),
    ];

    $element['iframe']['width'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->stringTranslation->translate('iFrame width'),
      '#size' => 10,
      '#field_prefix' => 'width="',
      '#field_suffix' => '"',
      '#default_value' => !empty($settings['iframe']['width']) ? $settings['iframe']['width'] : '300',
    ];

    $element['iframe']['height'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->stringTranslation->translate('iFrame height'),
      '#size' => 10,
      '#field_prefix' => 'height="',
      '#field_suffix' => '"',
      '#default_value' => !empty($settings['iframe']['height']) ? $settings['iframe']['height'] : '250',
    ];

    $element['iframe']['title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->stringTranslation->translate('Value for the title attribute'),
      '#size' => 30,
      '#field_prefix' => '&lt;title&gt;',
      '#field_suffix' => '&lt;/title&gt;',
      '#default_value' => !empty($settings['title']) ? $settings['title'] : 'fbinstantarticles',
    ];

    $element['iframe']['atf_channel'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->stringTranslation->translate('Value for the atf_channel variable'),
      '#size' => 30,
      '#field_prefix' => 'atf_channel = "',
      '#field_suffix' => '"',
      '#default_value' => !empty($settings['atf_channel']) ? $settings['atf_channel'] : 'facebookinstantarticles',
    ];

    return $element;
  }

}
