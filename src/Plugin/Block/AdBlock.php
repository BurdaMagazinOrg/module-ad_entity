<?php

namespace Drupal\ad_entity\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines Advertising blocks.
 *
 * @Block(
 *   id = "ad_entity_block",
 *   admin_label = @Translation("Advertising block")
 * )
 */
class AdBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The storage for Advertising entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $adEntityStorage;

  /**
   * The view builder for Advertising entities.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $adEntityViewBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $type_manager = $container->get('entity_type.manager');
    $ad_entity_storage = $type_manager->getStorage('ad_entity');
    $ad_entity_view_builder = $type_manager->getViewBuilder('ad_entity');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $ad_entity_storage,
      $ad_entity_view_builder
    );
  }

  /**
   * AdBlock constructor.
   *
   * @param array $configuration
   *   The configuration array.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $ad_entity_storage
   *   The storage for Advertising entities.
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $ad_entity_view_builder
   *   The view builder for Advertising entities.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $ad_entity_storage, EntityViewBuilderInterface $ad_entity_view_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->adEntityStorage = $ad_entity_storage;
    $this->adEntityViewBuilder = $ad_entity_view_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $entities = $this->adEntityStorage->loadMultiple();
    $options = [];
    foreach ($entities as $entity) {
      $options[$entity->id()] = $entity->label();
    }
    $form['ad_entity_id'] = [
      '#type' => 'select',
      '#title' => $this->t("Advertising entity"),
      '#description' => $this->t("The selected Advertising entity will be displayed inside this block."),
      '#empty_value' => '',
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => !empty($this->configuration['ad_entity_id']) ? $this->configuration['ad_entity_id'] : NULL,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['ad_entity_id']
      = $form_state->getValue('ad_entity_id');
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $config = $this->getConfiguration();
    if (!empty($config['ad_entity_id'])) {
      return ['config' => ['ad_entity.ad_entity.' . $config['ad_entity_id']]];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $id = !empty($this->configuration['ad_entity_id']) ? $this->configuration['ad_entity_id'] : NULL;
    if ($id && ($ad_entity = $this->adEntityStorage->load($id))) {
      if ($ad_entity->access('view')) {
        return $this->adEntityViewBuilder->view($ad_entity);
      }
    }
    return [];
  }

}
