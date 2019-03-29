<?php

namespace Drupal\lit_xml\Utils\Ws\Transformers;

use Drupal\lit_xml\BaseTransformer;
use Drupal\node\Entity\Node;

/**
 * Class ReviewTransformer
 * @package Drupal\lit_xml\Transformers
 */
class ReviewTransformer extends BaseTransformer {

  /**
   * @inheritdoc
   */
  public static function transform(Node $entity): array {
    $author = $entity->getOwner();

    $description = $entity->get('field_review_body')->value;

    $result = [
      'pageid' => $entity->id(),
      'category' => $entity->bundle(),
      'title' => $entity->label(),
      'description' => self::teaser($description),
      'subjectperson' => NULL,
      'subjecttitle' => NULL,
      'isbn' => NULL,
      'pid' => NULL,
      'author' => $author->getDisplayName(),
      'status' => $entity->get('status')->value,
      'pubdate' => date('Y-m-d H:i:s', $entity->getCreatedTime()),
      'lastdate' => date('Y-m-d H:i:s', $entity->getChangedTime()),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up book fields.
    $book = $entity->get('field_review_reference_book')->referencedEntities()[0];
    if ($book) {
      $result['subjecttitle'] = $book->label();
      $result['subjectperson'] = $book->get('field_book_author')->value;
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

