<?php

namespace Drupal\lit_xml\Utils\Ws\Transformers;

use Drupal\lit_xml\BaseTransformer;
use Drupal\node\Entity\Node;

/**
 * Class AnalysisTransformer
 * @package Drupal\lit_xml\Transformers
 */
class AnalysisTransformer extends BaseTransformer {

  /**
   * @inheritdoc
   */
  public static function transform(Node $entity): array {
    $author = $entity->getOwner();
    $description = $entity->get('field_analysis_excerpt')->value;

    $result = [
      'pageid' => $entity->id(),
      'category' => $entity->bundle(),
      'title' => $entity->label(),
      'description' => self::teaser($description),
      'author' => $author->getDisplayName(),
      'subjecttitle' => NULL,
      'subjectperson' => NULL,
      'isbn' => NULL,
      'terms' => [],
      'pubdate' => date('Y-m-d H:i:s', $entity->getCreatedTime()),
      'lastdate' => date('Y-m-d H:i:s', $entity->getChangedTime()),
      'status' => $entity->get('status')->value,
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up book fields.
    $book = $entity->get('field_analysis_reference_book')->referencedEntities()[0];
    if ($book) {
      $result['subjecttitle'] = $book->label();
      $result['subjectperson'] = $book->get('field_book_author')->value;
      $result['isbn'] = $book->get('field_book_isbn')->value;
      $result['pid'] = $book->get('field_book_pid')->value;
    }

    // Set up tags field.
    if (\Drupal::config('lit_xml.settings')->get('terms')) {
      $terms = $entity->get('field_analysis_general_tags')->referencedEntities();
      $result['terms'] = array_map(function ($item) {
        return $item->label();
      }, $terms);
    }

    return $result;
  }

}

