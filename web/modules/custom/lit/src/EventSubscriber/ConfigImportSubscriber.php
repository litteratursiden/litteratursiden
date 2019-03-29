<?php

namespace Drupal\lit\EventSubscriber;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for importing configs.
 */
class ConfigImportSubscriber implements EventSubscriberInterface {

  /**
   * @const string
   */
  public const ABOUT_PAGE_UUID = '92723526-4252-4cef-93a6-85c046011f4b';

  /**
   * @const string
   */
  public const ABOUT_PAGE_MENU_LINK_UUID = 'e61539cf-580f-4b8e-97ec-2cc58cf96ea8';

  /**
   * @const string
   */
  public const BLOCK_TOPIC_MONTH_UUID = '00a7bc0e-de09-4b6c-9a53-92995c82548b';

  /**
   * @const string
   */
  public const BLOCK_BOOK_MONTH_UUID = '79085ea6-03cb-4337-90fe-ad2e82c67386';

  /**
   * @const string
   */
  public const BLOCK_PRICES_UUID = '798cc17d-0649-4bef-b979-f5b4b1fd7907';

  /**
   * @const string
   */
  public const BLOCK_AUTHOR_RECOMMEND_UUID = '839e05fe-ca6f-440b-903f-98ce057a64f7';

  /**
   * @const string
   */
  public const BLOCK_EDITORS_RECOMMEND_UUID = 'd46fb841-c4f8-42ad-94bf-bc2785518710';

  /**
   * @const string
   */
  public const BLOCK_DEBUTANTES_UUID = '2e4e92f8-cf74-4fe2-b4f3-0c7a8938ce0e';

  /**
   * @const string
   */
  public const BLOCK_WRITERS_UUID = '6a02ab29-feba-4e8c-9c89-546c58b16588';

  /**
   * @const string
   */
  public const BLOCK_BOOKS_DEBATED_UUID = '770195ea-29c5-4cf4-b4c0-effcd88d0379';

  /**
   * @const string
   */
  public const BLOCK_WINNINGS_AUTHORS_UUID = 'a5c8938a-202e-4ff8-809f-6ed5c0f48925';

  /**
   * @const string
   */
  public const DEFAULT_BACKGROUND_COLOR = '#3a3b3d';

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::IMPORT][] = ['onConfigImport', 50];
    return $events;
  }

  /**
   * Config import event listener.
   */
  public function onConfigImport(ConfigImporterEvent $event) {
    $this->createDefaultBlocks();
    $this->createDefaultNodes();
  }

  /**
   * Creates default nodes.
   */
  protected function createDefaultNodes() {
    $node_types = array_keys(NodeType::loadMultiple());
    if (in_array('page', $node_types) && !$this->isContentExists('node', self::ABOUT_PAGE_UUID)) {
      $about_page = Node::create([
        'type' => 'page',
        'title' => 'About Litteratursiden',
        'body' => [
          'value' => '<p>Information about Litteratursiden will be here.</p>',
          'format' => 'full_html',
        ],
        'uuid' => self::ABOUT_PAGE_UUID,
      ]);
      $about_page->save();

      if (!$this->isContentExists('menu_link_content', self::ABOUT_PAGE_MENU_LINK_UUID)) {
        $menu_link = MenuLinkContent::create([
          'title' => 'About Litteratursiden',
          'link' => ['uri' => "entity:node/{$about_page->id()}"],
          'menu_name' => 'footer',
          'weight' => -49,
          'bundle' => 'menu_link_content',
          'uuid' => self::ABOUT_PAGE_MENU_LINK_UUID,
        ]);
        $menu_link->save();
      }
    }
  }

  /**
   * Gets random node ids.
   *
   * @param string $type
   *   Node type which IDs should be returned.
   * @param array $required_fields
   *   Array of required field names in nodes.
   * @param int $amount
   *   Amount of node ids to return.
   *
   * @return array
   *   Array of node IDs.
   */
  protected function getRandomNodeIds($type, $required_fields = [], $amount = 1) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', $type)
      ->range(rand(0, 10), $amount);

    foreach ($required_fields as $field) {
      $query->exists($field);
    }

    $node_ids = $query->execute();
    return $node_ids;
  }

  /**
   * Checks whether content exists by uuid.
   *
   * @param string $entity_type
   *   Entity type which content will be checked.
   * @param string $uuid
   *   Content uuid which should be checked.
   *
   * @return bool
   *   Whether block exists with a given uuid.
   */
  protected function isContentExists($entity_type, $uuid) {
    return (bool) \Drupal::entityQuery($entity_type)
      ->condition('uuid', $uuid)
      ->execute();
  }

  /**
   * Creates default content blocks.
   */
  protected function createDefaultBlocks() {
    $block_types = array_keys(BlockContentType::loadMultiple());
    $blocks = [
      [
        'type' => 'spot',
        'info' => 'Topic of the month',
        'field_spot_content' => $this->getRandomNodeIds('topic', ['field_topic_spotbox_image']),
        'uuid' => self::BLOCK_TOPIC_MONTH_UUID,
      ],
      [
        'type' => 'spot',
        'info' => 'Book of the month',
        'field_spot_content' => $this->getRandomNodeIds('book', ['field_book_spotbox_image']),
        'uuid' => self::BLOCK_BOOK_MONTH_UUID,
      ],
      [
        'type' => 'content_carousel',
        'info' => 'Prices',
        'field_content_carousel_reference' => $this->getRandomNodeIds('book', ['field_book_cover_image'], 20),
        'field_content_carousel_bg' => self::DEFAULT_BACKGROUND_COLOR,
        'uuid' => self::BLOCK_PRICES_UUID,
      ],
      [
        'type' => 'content_carousel',
        'info' => 'Author recommendations',
        'field_content_carousel_reference' => $this->getRandomNodeIds('book', ['field_book_cover_image'], 20),
        'field_content_carousel_bg' => self::DEFAULT_BACKGROUND_COLOR,
        'uuid' => self::BLOCK_AUTHOR_RECOMMEND_UUID,
      ],
      [
        'type' => 'content_carousel',
        'info' => 'The editors recommend',
        'field_content_carousel_reference' => $this->getRandomNodeIds('author_portrait', [], 20),
        'field_content_carousel_bg' => self::DEFAULT_BACKGROUND_COLOR,
        'uuid' => self::BLOCK_EDITORS_RECOMMEND_UUID,
      ],
      [
        'type' => 'content_carousel',
        'info' => 'Debutantes',
        'field_content_carousel_reference' => $this->getRandomNodeIds('author_portrait', [], 20),
        'field_content_carousel_bg' => self::DEFAULT_BACKGROUND_COLOR,
        'uuid' => self::BLOCK_DEBUTANTES_UUID,
      ],
      [
        'type' => 'content_carousel',
        'info' => 'Classical Danish writers',
        'field_content_carousel_reference' => $this->getRandomNodeIds('author_portrait', [], 20),
        'field_content_carousel_bg' => self::DEFAULT_BACKGROUND_COLOR,
        'uuid' => self::BLOCK_WRITERS_UUID,
      ],
      [
        'type' => 'similar_content_tabs',
        'info' => 'Books being debated',
        'field_similar_content_reference' => $this->getRandomNodeIds('similar', [], 4),
        'field_similar_content_bg' => self::DEFAULT_BACKGROUND_COLOR,
        'uuid' => self::BLOCK_BOOKS_DEBATED_UUID,
      ],
      [
        'type' => 'similar_content_tabs',
        'info' => 'Award winnings authors',
        'field_similar_content_reference' => $this->getRandomNodeIds('similar', [], 4),
        'field_similar_content_bg' => self::DEFAULT_BACKGROUND_COLOR,
        'uuid' => self::BLOCK_WINNINGS_AUTHORS_UUID,
      ],
    ];

    foreach ($blocks as $block) {
      if (in_array($block['type'], $block_types) && !$this->isContentExists('block_content', $block['uuid'])) {
        BlockContent::create($block)->save();
      }
    }
  }

}
