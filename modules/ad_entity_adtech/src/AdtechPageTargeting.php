<?php

namespace Drupal\ad_entity_adtech;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ad_entity\TargetingCollection;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * The service class for building AdTech Factory page targeting.
 */
class AdtechPageTargeting {

  /**
   * The global AdTech Factory settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The (already) built page targeting.
   *
   * @var \Drupal\ad_entity\TargetingCollection
   */
  protected $pageTargeting;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * AdtechPageTargeting constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, TransliterationInterface $transliteration) {
    if ($global_config = $config_factory->get('ad_entity.settings')) {
      $this->settings = $global_config->get('adtech_factory');
    }
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->transliteration = $transliteration;
  }

  /**
   * Get the page targeting for the current page.
   *
   * @return \Drupal\ad_entity\TargetingCollection
   *   The page targeting.
   */
  public function get() {
    if (!isset($this->pageTargeting)) {
      if (empty($this->settings['page_targeting'])) {
        $page_targeting = new TargetingCollection();
      }
      else {
        $page_targeting = new TargetingCollection($this->settings['page_targeting']);
      }
      $this->build($page_targeting);
      $page_targeting->filter();
      $this->pageTargeting = $page_targeting;
    }
    return $this->pageTargeting;
  }

  /**
   * Build up the current page targeting.
   *
   * @param \Drupal\ad_entity\TargetingCollection $page_targeting
   *   The current page targeting to build.
   */
  protected function build(TargetingCollection $page_targeting) {
    if (empty($this->settings['include_content_info']) || !($page_entity = $this->getPageEntity())) {
      return;
    }

    // @todo Set contenttype according to specification.
    //  $page_targeting->set('contenttype', '??');

    $channel_vocabulary = !empty($this->settings['channel_vocabulary']) ? $this->settings['channel_vocabulary'] : 'channel';
    $channel_term = NULL;
    foreach ($page_entity->referencedEntities() as $referenced) {
      if ($referenced->getEntityTypeId() === 'taxonomy_term' && $referenced->bundle() === $channel_vocabulary) {
        $channel_term = $referenced;
        break;
      }
    }
    if (!isset($channel_term)) {
      return;
    }
    // @todo Set channel, subchannel according to specification.
    // $langcode = $channel_term->language()->getId();
    // $page_targeting->set('channel', $this->sanitize($channel_term->label(), $langcode));
  }

  /**
   * Sanitize the given string value for AdTech requirements.
   *
   * @param string $value
   *   The value to sanitize.
   * @param string $langcode
   *   The language code of the language the value is in.
   * @param string $type
   *   Whether it's a targeting 'key' or 'value'.
   *
   * @return string
   *   The sanitized value.
   */
  protected function sanitize($value, $langcode = 'en', $type = 'value') {
    // Replace whitespace.
    $value = str_replace(' ', '-', $value);
    // Lowercase string.
    $value = strtolower($value);
    // Transliterate the string.
    $value = $this->transliteration
      ->transliterate($value, $langcode, '');
    // Remove multiple dashes.
    $value = preg_replace("/--+/", '-', $value);
    // Remove remaining unsafe characters.
    $value = preg_replace('/[^0-9a-z_-]/', '', $value);
    // Restrict to the maximum allowed string length.
    $value = ($type === 'key') ? substr($value, 0, 20) : substr($value, 0, 40);

    return $value;
  }

  /**
   * Get the current page entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The page entity, if given and accessible.
   */
  protected function getPageEntity() {
    $page_entity = NULL;
    foreach ($this->routeMatch->getParameters() as $param) {
      if ($param instanceof EntityInterface) {
        $page_entity = $param;
        break;
      }
    }
    if (!isset($page_entity)) {
      // Some routes don't properly define entity parameters.
      // Thus, try to load them by its raw Id, if given.
      $types = $this->entityTypeManager->getDefinitions();
      foreach ($this->routeMatch->getParameters()->keys() as $param_key) {
        if (!isset($types[$param_key])) {
          continue;
        }
        if ($param = $this->routeMatch->getParameter($param_key)) {
          if (is_string($param) || is_numeric($param)) {
            try {
              $page_entity = $this->entityTypeManager->getStorage($param_key)->load($param);
            }
            catch (\Exception $e) {
            }
          }
          break;
        }
      }
    }
    if (!isset($page_entity) || !$page_entity->access('view')) {
      return NULL;
    }
    return $page_entity;
  }

}
