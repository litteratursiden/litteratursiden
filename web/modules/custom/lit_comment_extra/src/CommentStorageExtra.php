<?php

namespace Drupal\lit_comment_extra;

use Drupal\comment\CommentStorage;
use Drupal\comment\CommentInterface;
use Drupal\comment\CommentManagerInterface;
use Drupal\comment\Entity\Comment;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the storage handler class with extra functionality for comments.
 */
class CommentStorageExtra extends CommentStorage implements CommentStorageExtraInterface {

  /**
   * {@inheritdoc}
   */
  public function loadMoreThread(EntityInterface $entity, $field_name, $mode, $comments_per_page, $comment_id = 0) {
    $query = $this->database->select('comment_field_data', 'c');
    $query->addField('c', 'cid');
    $query
      ->condition('c.entity_id', $entity->id())
      ->condition('c.entity_type', $entity->getEntityTypeId())
      ->condition('c.field_name', $field_name)
      ->condition('c.default_langcode', 1)
      ->addTag('entity_access')
      ->addTag('comment_filter')
      ->addMetaData('base_table', 'comment')
      ->addMetaData('entity', $entity)
      ->addMetaData('field_name', $field_name);

    if ($comments_per_page) {
      $query = $query->range(0, $comments_per_page);
    }

    if (!$this->currentUser->hasPermission('administer comments')) {
      $query->condition('c.status', CommentInterface::PUBLISHED);
    }

    if ($mode == CommentManagerInterface::COMMENT_MODE_FLAT) {
      $query->orderBy('c.cid', 'DESC');
      if ($comment_id) {
        $query->condition('c.cid', $comment_id, '<');
      }
    }
    else {
      $query->orderBy('c.thread', 'DESC');

      if ($comment_id && $comment = Comment::load($comment_id)) {
        $query->condition('c.thread', $comment->getThread(), '<');
      }
    }

    $cids = $query->execute()->fetchCol();

    $comments = [];
    if ($cids) {
      $comments = $this->loadMultiple($cids);
    }

    return $comments;
  }

}
