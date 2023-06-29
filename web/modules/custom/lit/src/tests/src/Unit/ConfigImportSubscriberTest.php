<?php

namespace Drupal\Tests\lit\Unit;

use Drupal\block_content\Entity\BlockContentType;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lit\EventSubscriber\ConfigImportSubscriber as Subscriber;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Class ConfigImportSubscriber Test.
 *
 * @coversDefaultClass \Drupal\lit\EventSubscriber\ConfigImportSubscriber
 * @group lit
 */
class ConfigImportSubscriberTest extends KernelTestBase {
  /**
   * An existing node UUID.
   *
   * @const string
   */
  protected const EXISTING_NODE_UUID = '92723526-4252-4cef-93a6-85c046018f4b';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'block_content',
    'system',
    'field',
    'link',
    'image',
    'text',
    'file',
    'menu_link_content',
    'node',
    'user',
  ];

  /**
   * The content types that needs to be generated.
   *
   * @var array
   */
  private $contentTypes = [
    'author_portrait',
    'book',
    'similar',
    'topic',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('block');
    $this->installEntitySchema('block_content');

    $this->installConfig(['field', 'file', 'node', 'system']);

    // Create topic content type.
    NodeType::create([
      'type' => 'topic',
      'name' => 'Topic',
    ])->save();
    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_topic_spotbox_image',
      'type' => 'image',
      'translatable' => '0',
    ])->save();
    FieldConfig::create([
      'label' => 'test label',
      'description' => '',
      'field_name' => 'field_topic_spotbox_image',
      'entity_type' => 'node',
      'bundle' => 'topic',
      'required' => 0,
    ])->save();

    // Create book content type.
    NodeType::create([
      'type' => 'book',
      'name' => 'Book',
    ])->save();
    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_book_spotbox_image',
      'type' => 'image',
      'translatable' => '0',
    ])->save();
    FieldConfig::create([
      'label' => 'test label',
      'description' => '',
      'field_name' => 'field_book_spotbox_image',
      'entity_type' => 'node',
      'bundle' => 'book',
      'required' => 0,
    ])->save();
    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_book_cover_image',
      'type' => 'image',
      'translatable' => '0',
    ])->save();
    FieldConfig::create([
      'label' => 'test label',
      'description' => '',
      'field_name' => 'field_book_cover_image',
      'entity_type' => 'node',
      'bundle' => 'book',
      'required' => 0,
    ])->save();

    // Create spot block type.
    BlockContentType::create([
      'id' => 'spot',
      'label' => 'Spot',
      'revision' => FALSE,
    ])->save();

    // Create content_carousel block type.
    BlockContentType::create([
      'id' => 'content_carousel',
      'label' => 'Content carousel',
      'revision' => FALSE,
    ])->save();

    // Create similar_content_tabs block type.
    BlockContentType::create([
      'id' => 'similar_content_tabs',
      'label' => 'Similar content tabs',
      'revision' => FALSE,
    ])->save();

    // Create demo content.
    foreach ($this->contentTypes as $type) {
      $this->createContent($type, 20);
    }
  }

  /**
   * Test checking if the content exist.
   *
   * @covers ::isContentExists
   */
  public function testIsContentExists() {
    // Get a reflected, accessible version of the protected ::isContentExists()
    $method = $this->getAccessibleMethod(Subscriber::class, 'isContentExists');

    $node = Node::create([
      'uuid' => self::EXISTING_NODE_UUID,
      'type' => 'page',
      'title' => 'Existing page',
    ]);
    $node->save();

    $subscriber = new Subscriber();
    $exist = $method->invokeArgs($subscriber, ['node', self::EXISTING_NODE_UUID]);

    $this->assertTrue($exist);
  }

  /**
   * Test creating default nodes.
   *
   * We want to test a protected method on a class.This is problematic
   * because, by design, we don't have access to this method. However,
   * we do have a tool available to help us out with this problem:
   * We can override the accessibility of a method using reflection.
   *
   * @covers ::createDefaultNodes
   */
  public function testCreatingDefaultNodes() {
    // Get a reflected, accessible version of the protected
    // ::createDefaultNodes() method.
    $method = $this->getAccessibleMethod(Subscriber::class, 'createDefaultNodes');

    $subscriber = new Subscriber();
    $method->invoke($subscriber);

    $entity = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'uuid' => Subscriber::ABOUT_PAGE_UUID,
      ]);

    $this->assertNotNull($entity);
  }

  /**
   * Test getting random node ids.
   *
   * @covers ::getRandomNodeIds
   */
  public function testGetRandomNodeIds() {
    // Get a reflected, accessible version of the protected
    // ::getRandomNodeIds() method.
    $method = $this->getAccessibleMethod(Subscriber::class, 'getRandomNodeIds');

    $subscriber = new Subscriber();
    $first = $method->invokeArgs($subscriber, ['topic', [], 1]);
    $second = $method->invokeArgs($subscriber, ['topic', [], 1]);

    $this->assertInternalType('array', $first);
    $this->assertCount(1, $first);
    $this->assertNotEquals($first, $second);
  }

  /**
   * Test creating default blocks.
   *
   * @covers ::createDefaultBlocks
   */
  public function testCreatingDefaultBlocks() {
    // Get a reflected, accessible version of the protected
    // ::createDefaultBlocks() method.
    $method = $this->getAccessibleMethod(Subscriber::class, 'createDefaultBlocks');

    $subscriber = new Subscriber();
    $method->invoke($subscriber);

    $count = $entity = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->getQuery()
      ->condition('uuid', [
        Subscriber::BLOCK_TOPIC_MONTH_UUID,
        Subscriber::BLOCK_BOOK_MONTH_UUID,
        Subscriber::BLOCK_PRICES_UUID,
        Subscriber::BLOCK_AUTHOR_RECOMMEND_UUID,
        Subscriber::BLOCK_EDITORS_RECOMMEND_UUID,
        Subscriber::BLOCK_DEBUTANTES_UUID,
        Subscriber::BLOCK_WRITERS_UUID,
        Subscriber::BLOCK_BOOKS_DEBATED_UUID,
        Subscriber::BLOCK_WINNINGS_AUTHORS_UUID,
      ], 'IN')
      ->count()
      ->execute();

    $this->assertEquals(9, $count);
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

  /**
   * Create demo topic for the test.
   *
   * @param string $type
   *   THe content type.
   * @param int $number
   *   A number.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createContent(string $type, int $number = 1): void {
    for ($i = 0; $i < $number; $i++) {
      $node = Node::create([
        'type' => $type,
        'title' => $type . $i,
      ]);

      $node->save();
    }
  }

}
