<?php

namespace Drupal\lit_xml\Utils\DocBook\Transformers;

use Drupal\lit_xml\BaseTransformer;
use Drupal\node\Entity\Node;

/**
 * Class BookListTransformer
 * @package Drupal\lit_xml\Transformers
 */
class BookListTransformer extends BaseTransformer {

  /**
   * @inheritdoc
   */
  public static function transform(Node $entity): array {
    $user = $entity->getOwner();

    $description = $entity->get('field_book_list_body')->value;

    $result = [
      'nid' => $entity->id(),
      'type' => $entity->bundle(),
      'title' => $entity->label(),
      'list' => self::teaser($description, 2000),
      'abstract' => self::teaser($description),
      'name' => $user->getDisplayName(),
      'status' => $entity->get('status')->value,
      'date' => $entity->getChangedTime(),
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up books.
    $books = $entity->get('field_book_list_reference_book')->referencedEntities();
    $result['books'] = array_map(function (Node $item) {
      return [
        'title' => $item->label(),
        'author' => $item->get('field_book_author')->value,
        'isbn' => $item->get('field_book_isbn')->value,
        'pid' => $item->get('field_book_pid')->value
      ];
    }, $books);

    return $result;
  }

}

