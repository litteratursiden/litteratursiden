<?php

namespace Drupal\lit_xml\Utils\DkAbm\Transformers;

use Drupal\lit_xml\BaseTransformer;
use Drupal\node\Entity\Node;

/**
 * Class ReviewTransformer
 * @package Drupal\lit_xml\Utils\DkAbm\Transformers
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
      'teaser' => self::teaser($description),
      'author_book' => NULL,
      'title_book' => NULL,
      'isbn' => NULL,
      'pid' => NULL,
      'xsi-type' => 'Anmeldelse',
      'name' => $author->getDisplayName(),
      'status' => $entity->get('status')->value,
      'created' => $entity->getCreatedTime(),
      'changed' => $entity->getChangedTime(),
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up book fields.
    $book = $entity->get('field_review_reference_book')->referencedEntities()[0];
    if ($book) {
      $result['title_book'] = $book->label();
      $result['author_book'] = $book->get('field_book_author')->value;
      $result['isbn'] = $book->get('field_book_isbn')->value;
      $result['pid'] = $book->get('field_book_pid')->value;
    }

    if (\Drupal::config('lit_xml.settings')->get('terms')) {
      $terms = $entity->get('field_review_generel_tags')->referencedEntities();
      $result['terms'] = array_map(function ($item) {
        return $item->label();
      }, $terms);
    }

    return $result;
  }

}

