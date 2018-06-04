<?php

namespace Drupal\Tests\ad_entity\Kernel;

use Drupal\ad_entity\Entity\AdEntityInterface;
use Drupal\ad_entity_test\Plugin\ad_entity\AdType\TestType;
use Drupal\ad_entity_test\Plugin\ad_entity\AdView\TestView;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the ad_entity type.
 *
 * @coversDefaultClass \Drupal\ad_entity\Entity\AdEntity
 * @group ad_entity
 */
class AdEntityTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'ad_entity',
    'ad_entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['ad_entity']);
  }

  /**
   * Creates a new ad_entity instance.
   *
   * @return \Drupal\ad_entity\Entity\AdEntityInterface
   *   The created ad_entity instance.
   */
  protected function createNew() {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    try {
      $storage = $this->container->get('entity_type.manager')->getStorage('ad_entity');
    }
    catch (\Exception $e) {
      return NULL;
    }
    $values = [
      'id' => 'test_entity',
      'label' => 'Test entity',
      'status' => TRUE,
      'type_plugin_id' => 'test_type',
      'view_plugin_id' => 'test_view',
    ];
    return $storage->create($values);
  }

  /**
   * Test the creation of an ad_entity instance.
   */
  public function testCreation() {
    $ad_entity = $this->createNew();
    $this->assertTrue($ad_entity instanceof AdEntityInterface);
  }

  /**
   * Test for assigned plugins to expect.
   */
  public function testPluginsAssigned() {
    $ad_entity = $this->createNew();
    $type_plugin = $ad_entity->getTypePlugin();
    $this->assertTrue($type_plugin instanceof TestType);
    $view_plugin = $ad_entity->getViewPlugin();
    $this->assertTrue($view_plugin instanceof TestView);
  }

}
