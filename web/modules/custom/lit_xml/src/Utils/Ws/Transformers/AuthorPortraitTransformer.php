<?php

namespace Drupal\lit_xml\Utils\Ws\Transformers;

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
    $author = $entity->getOwner();

    $description = $entity->get('field_author_portrait_body')->value;
    $firstname = $entity->get('field_author_portrait_first_name')->value;
    $lastname = $entity->get('field_author_portrait_surname')->value;

    $result = [
      'pageid' => $entity->id(),
      'category' => $entity->bundle(),
      'title' => $entity->label(),
      'status' => $entity->get('status')->value,
      'pubdate' => date('Y-m-d H:i:s', $entity->getCreatedTime()),
      'lastdate' => date('Y-m-d H:i:s', $entity->getChangedTime()),
      'author' => $author->getDisplayName(),
      'description' => self::teaser($description),
      'subjectperson_firstname' => $firstname,
      'subjectperson_lastname' => $lastname,
      'subjectperson' => $firstname . ' ' . $lastname,
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    if (\Drupal::config('lit_xml.settings')->get('terms')) {
      $terms = $entity->get('field_author_portrait_tags')->referencedEntities();
      $result['terms'] = array_map(function ($item) {
        return $item->label();
      }, $terms);
    }

    return $result;
  }

}

