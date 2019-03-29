<?php

namespace Drupal\lit_xml\Utils\DkAbm\Transformers;

use Drupal\lit_xml\BaseTransformer;
use Drupal\node\Entity\Node;

/**
 * Class InterviewTransformer
 * @package Drupal\lit_xml\Transformers
 */
class InterviewTransformer extends BaseTransformer {

  /**
   * @inheritdoc
   */
  public static function transform(Node $entity): array {
    $author = $entity->getOwner();

    $referencedAuthor = $entity->get('field_interview_reference_author')->referencedEntities()[0];
    $firstname = $referencedAuthor ? $referencedAuthor->get('field_author_portrait_first_name')->value : '';
    $lastname = $referencedAuthor ? $referencedAuthor->get('field_author_portrait_surname')->value : '';

    $description = $entity->get('field_interview_body')->value;

    $result = [
      'nid' => $entity->id(),
      'type' => $entity->bundle(),
      'title' => $entity->label(),
      'teaser' => self::teaser($description),
      'author_book' => $lastname . ' ' . $firstname,
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

