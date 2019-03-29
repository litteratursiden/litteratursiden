<?php

namespace Drupal\lit_xml\Utils\DocBook\Transformers;

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
      'interview' => self::teaser($description, 2000),
      'abstract' => self::teaser($description),
      'author' => $firstname . ' ' . $lastname,
      'name' => $author->getDisplayName(),
      'status' => $entity->get('status')->value,
      'date' => $entity->getChangedTime(),
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    // Set up external links field.
    $external_links = $entity->get('field_interview_external_links')->getValue();
    $internal_links = $entity->get('field_interview_internal_lin')->getValue();
    $result['links'] = array_map(function ($item) {
      return [
        'title' => $item['title'],
        'url' => $item['uri'],
      ];
    }, array_merge($external_links, $internal_links));

    return $result;
  }

}

