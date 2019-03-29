<?php

namespace Drupal\lit_xml\Utils\DkAbm\Transformers;

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
      'teaser' => self::teaser($description),
      'xsi-type' => 'Artikel',
      'name' => $author->getDisplayName(),
      'status' => $entity->get('status')->value,
      'created' => $entity->getCreatedTime(),
      'changed' => $entity->getChangedTime(),
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    return $result;
  }

}

