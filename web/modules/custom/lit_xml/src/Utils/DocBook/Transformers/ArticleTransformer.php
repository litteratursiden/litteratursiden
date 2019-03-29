<?php

namespace Drupal\lit_xml\Utils\DocBook\Transformers;

use Drupal\lit_xml\BaseTransformer;
use Drupal\node\Entity\Node;

/**
 * Class ArticleTransformer
 * @package Drupal\lit_xml\Transformers
 */
class ArticleTransformer extends BaseTransformer {

  /**
   * @inheritdoc
   */
  public static function transform(Node $entity): array {
    $author = $entity->getOwner();

    $description = $entity->get('field_article_body')->value;

    $result = [
      'nid' => $entity->id(),
      'type' => $entity->bundle(),
      'title' => $entity->label(),
      'artikel' => self::teaser($description, 2000),
      'abstract' => self::teaser($description),
      'name' => $author->getDisplayName(),
      'status' => $entity->get('status')->value,
      'date' => $entity->getChangedTime(),
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up external links field.
    $external_links = $entity->get('field_article_link_external')->getValue();
    $internal_links = $entity->get('field_article_internal_link')->getValue();
    $result['links'] = array_map(function ($item) {
      return [
        'title' => $item['title'],
        'url' => $item['uri'],
      ];
    }, array_merge($external_links, $internal_links));

    return $result;
  }

}

