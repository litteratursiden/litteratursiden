<?php

namespace Drupal\lit_taxonomy;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\TermAccessControlHandler as BaseTermAccessControlHandler;

/**
 * Defines the access control handler for the taxonomy term entity type.
 *
 * @see \Drupal\taxonomy\Entity\Term
 */
class TermAccessControlHandler extends BaseTermAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return Term::access($entity, $operation, $account);
  }

}
