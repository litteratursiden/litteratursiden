<?php

namespace Drupal\lit_xml\Utils\DocBook\Transformers;

use Drupal\lit_xml\BaseTransformer;
use Drupal\node\Entity\Node;

/**
 * Class ReviewTransformer
 * @package Drupal\lit_xml\Utils\DocBook\Transformers
 */
class ReviewTransformer extends BaseTransformer {

  /**
   * @inheritdoc
   */
  public static function transform(Node $entity): array {
    $author = $entity->getOwner();

    $description = $entity->get('field_review_body')->value;

    $result = [
      'nid' => $entity->id(),
      'type' => $entity->bundle(),
      'title' => $entity->label(),
      'recommendation' => self::teaser($description, 2000),
      'abstract' => self::teaser($description),
      'book_title' => NULL,
      'author' => NULL,
      'isbn' => NULL,
      'pid' => NULL,
      'name' => $author->getDisplayName(),
      'status' => $entity->get('status')->value,
      'date' => $entity->getChangedTime(),
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up book fields.
    $book = $entity->get('field_review_reference_book')->referencedEntities()[0];
    if ($book) {
      $result['book_title'] = $book->label();
      $result['author'] = $book->get('field_book_author')->value;
      $result['isbn'] = $book->get('field_book_isbn')->value;
      $result['pid'] = $book->get('field_book_pid')->value;
    }

    // Set up external links field.
    $external_links = $entity->get('field_review_link_external')->getValue();
    $internal_links = $entity->get('field_review_internal_link')->getValue();
    $result['links'] = array_map(function ($item) {
      return [
        'title' => $item['title'],
        'url' => $item['uri'],
      ];
    }, array_merge($external_links, $internal_links));

    return $result;
  }

}

