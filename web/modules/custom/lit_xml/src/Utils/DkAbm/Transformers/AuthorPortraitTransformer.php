<?php

namespace Drupal\lit_xml\Utils\DkAbm\Transformers;

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
    $country = $entity->get('field_author_portrait_country')->value;

    $result = [
      'nid' => $entity->id(),
      'type' => $entity->bundle(),
      'title' => $entity->label(),
      'country' => $country,
      'teaser' => self::teaser($description),
      'firstname' => $firstname,
      'lastname' => $lastname,
      'isbn' => self::getISBN($entity->id()),
      'xsi-type' => 'Netdokument',
      'name' => $user->getDisplayName(),
      'created' => $entity->getCreatedTime(),
      'changed' => $entity->getChangedTime(),
      'status' => $entity->get('status')->value,
      'language' => $entity->language()->getId(),
      'url' => $entity->url('canonical', ['absolute' => TRUE]),
    ];

    return $result;
  }

  /**
   * @param $author_nid
   * @return string
   */
  private static function getISBN($author_nid) {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'book')
      ->condition('field_book_reference_author.target_id', $author_nid)
      ->range(0, 1);

    $nids = $query->execute();

    if ($nids && is_array($nids)) {
      $node = Node::load($nids[0]);

      if (NULL === $node) {
          return NULL;
      }

      $result = $node->get('field_book_isbn')->value;
    }

    return $result ?? NULL;
  }
}

