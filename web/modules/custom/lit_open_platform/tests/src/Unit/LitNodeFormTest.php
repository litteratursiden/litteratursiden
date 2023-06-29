<?php

namespace Drupal\Tests\lit_open_platform\Unit;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lit_open_platform\Form\LitNodeForm;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Unit tests for the lit node form.
 *
 * @group lit
 *
 * @coversDefaultClass \Drupal\lit_open_platform\Form\LitNodeForm
 */
class LitNodeFormTest extends KernelTestBase {

  /**
   * @const string
   */
  public const PID = '810015-katalog:005177353';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'node', 'user', 'field', 'text'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');

    $this->installConfig(['field', 'node', 'system']);

    // Create topic content type.
    NodeType::create([
      'type' => 'book',
      'name' => 'Book',
    ])->save();
    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_book_pid',
      'type' => 'string',
      'translatable' => '0',
    ])->save();
    FieldConfig::create([
      'label' => 'test label',
      'description' => '',
      'field_name' => 'field_book_pid',
      'entity_type' => 'node',
      'bundle' => 'book',
      'required' => 0,
    ])->save();
  }

  /**
   * Test getting pids.
   *
   * @covers ::getPids
   */
  public function testGetPids() {
    $ids = [
      ['target_id' => self::PID],
      ['target_id' => 1020],
    ];

    $form = $this->getMockBuilder(LitNodeForm::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Get a reflected, accessible version of the protected ::getPids() method.
    $method = $this->getAccessibleMethod(LitNodeForm::class, 'getPids');
    $pids = $method->invokeArgs($form, [$ids]);

    $this->assertEquals([self::PID], $pids);
  }

  /**
   * Test getting book by pid.
   *
   * @covers ::getBookByPid
   */
  public function testGetBookByPid() {
    Node::create([
      'type' => 'book',
      'title' => 'Test book',
      'field_book_pid' => self::PID,
    ])->save();

    $form = $this->getMockBuilder(LitNodeForm::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Get a reflected, accessible version of the protected ::getBookByPid() method.
    $method = $this->getAccessibleMethod(LitNodeForm::class, 'getBookByPid');
    $nid = $method->invokeArgs($form, [self::PID]);

    $this->assertNotFalse($nid);
  }

  /**
   * Get an accessible method using reflection.
   */
  public function getAccessibleMethod($class_name, $method_name) {
    $class = new \ReflectionClass($class_name);
    $method = $class->getMethod($method_name);
    $method->setAccessible(TRUE);
    return $method;
  }

}
