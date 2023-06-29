<?php

namespace Drupal\lit_taxonomy;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class Term.
 *
 * @package Drupal\lit_taxonomy
 */
class Term {

  /**
   * @param $term
   * @return bool
   */
  public static function isPublished($term): bool {
    return (bool) $term->get('status')->value;
  }

  /**
   * @param $term
   * @return bool
   */
  public static function getOwnerId($term): int {
    return (int) $term->get('uid')->getValue()[0]['target_id'];
  }

  /**
   * {@inheritdoc}
   */
  public static function access(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if (self::isPublished($entity)) {
          $result = AccessResult::allowedIfHasPermission($account, 'access content');
        }
        else {
          $viewOwnUnpublishedTaxonomy = $account->hasPermission('view own unpublished taxonomy term') && ($account->id() == self::getOwnerId($entity));
          $administerTaxonomy = $account->hasPermission('administer taxonomy');

          $result = AccessResult::allowedIf($administerTaxonomy || $viewOwnUnpublishedTaxonomy);
        }
        break;

      case 'update':
        $result = AccessResult::allowedIfHasPermissions($account, ["edit terms in {$entity->bundle()}", 'administer taxonomy'], 'OR');
        break;

      case 'delete':
        $result = AccessResult::allowedIfHasPermissions($account, ["delete terms in {$entity->bundle()}", 'administer taxonomy'], 'OR');
        break;

      default:
        // No opinion.
        $result = AccessResult::neutral();
        break;
    }

    return $result;
  }

}
