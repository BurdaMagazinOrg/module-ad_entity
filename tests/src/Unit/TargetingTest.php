<?php

namespace Drupal\Tests\ad_entity\Unit;

use Drupal\ad_entity\TargetingCollection;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the TargetingCollection class.
 *
 * @coversDefaultClass \Drupal\ad_entity\TargetingCollection
 * @group ad_entity
 */
class TargetingTest extends UnitTestCase {

  /**
   * Test the basic TargetingCollection methods.
   *
   * @covers ::get
   * @covers ::set
   * @covers ::add
   * @covers ::remove
   */
  public function testBasic() {
    $collection = new TargetingCollection();
    $this->assertNull($collection->get('testkey'));
    $collection->set('testkey', 'testval');
    $this->assertEquals('testval', $collection->get('testkey'));
    // When being set multiple times with the same key, ensure
    $collection->set('testkey', 'testval');
    $this->assertEquals('testval', $collection->get('testkey'));
    $collection->add('testkey', 'testval');
    $this->assertEquals('testval', $collection->get('testkey'));

    // Do not lose any values.
    $collection->add('testkey', 'testval2');
    $this->assertArrayEquals(['testval', 'testval2'], $collection->get('testkey'));

    $collection->remove('testkey');
    $this->assertNull($collection->get('testkey'));
  }

}
