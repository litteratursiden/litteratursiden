<?php

namespace Drupal\lit_xml\Utils\DocBook\Transformers;

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
      'nid' => $entity->id(),
      'type' => $entity->bundle(),
      'title' => $entity->label(),
      'analysis' => self::teaser($description, 2000),
      'abstract' => self::teaser($description),
      'name' => $author->getDisplayName(),
      'date' => $entity->getChangedTime(),
      'status' => $entity->get('status')->value,
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up book fields.
    $book = $entity->get('field_analysis_reference_book')->referencedEntities()[0];
    if ($book) {
      $result['book_title'] = $book->label();
      $result['author'] = $book->get('field_book_author')->value;
      $result['isbn'] = $book->get('field_book_isbn')->value;
      $result['pid'] = $book->get('field_book_pid')->value;
    }

    // Set up external links field.
    $links = $entity->get('field_analysis_external_link')->getValue();
    $result['links'] = array_map(function ($item) {
      return [
        'title' => $item['title'],
        'url' => $item['uri'],
      ];
    }, $links);

    return $result;
  }

}

