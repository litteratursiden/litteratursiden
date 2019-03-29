<?php

namespace Drupal\lit_xml\Utils\DocBook\Transformers;

use Drupal\lit_xml\BaseTransformer;
use Drupal\node\Entity\Node;

/**
 * Class AuthorPortraitTransformer
 * @package Drupal\lit_xml\Transformers
 */
class AuthorPortraitTransformer extends BaseTransformer {

  /**
   * @inheritdoc
   */
  public static function transform(Node $entity): array {
    $user = $entity->getOwner();
    $description = $entity->get('field_author_portrait_body')->value;
    $firstname = $entity->get('field_author_portrait_first_name')->value;
    $lastname = $entity->get('field_author_portrait_surname')->value;

    $result = [
      'nid' => $entity->id(),
      'type' => $entity->bundle(),
      'title' => $entity->label(),
      'om' => self::teaser($description, 2000),
      'abstract' => self::teaser($description),
      'author' => $firstname . ' ' . $lastname,
      'books' => [],
      'name' => $user->getDisplayName(),
      'date' => $entity->getChangedTime(),
      'status' => $entity->get('status')->value,
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up books.
    $books = self::getBooks($entity->id());
    foreach ($books as $book) {
      $isbn = $book->get('field_book_isbn')->value;
      $result['books'][$isbn] = $book->label();
    }

    // Set up external links field.
    $links = $entity->get('field_author_portrait_ext_link')->getValue();
    $result['links'] = array_map(function ($item) {
      return [
        'title' => $item['title'],
        'url' => $item['uri'],
      ];
    }, $links);

    return $result;
  }

  /**
   * @param $author_nid
   * @return \Drupal\Core\Entity\EntityInterface[]|static[]
   */
  private static function getBooks($author_nid) {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'book')
      ->condition('field_book_reference_author.target_id', $author_nid);

    $nids = $query->execute();

    return Node::loadMultiple($nids);
  }
}

