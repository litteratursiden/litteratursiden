<?php

namespace Drupal\lit_comment_extra;

use Drupal\comment\CommentStorageInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines an interface for comment extra storage class.
 */
interface CommentStorageExtraInterface extends CommentStorageInterface {

  /**
   * Retrieves comments for a thread, sorted in an order suitable for display.
   *
   * Unlike the loadThread function, default comments order is DESC and it
   * doesn't use pagination. Instead you can specify the latest comment ID
   * after which thread should be loaded.
   * It can be useful if you want to make a "load more button" instead
   * the default pagination.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose comment(s) needs rendering.
   * @param string $field_name
   *   The field_name whose comment(s) needs rendering.
   * @param int $mode
   *   The comment display mode: CommentManagerInterface::COMMENT_MODE_FLAT or
   *   CommentManagerInterface::COMMENT_MODE_THREADED.
   * @param int $comments_per_page
   *   (optional) The amount of comments to display per page.
   *   Defaults to 0, which means show all comments.
   * @param int $comment_id
   *   Comment ID after which other comments should be loaded.
   *   If it's 0, there're no restrictions.
   *
   * @return array
   *   Ordered array of comment objects, keyed by comment id.
   */
  public function loadMoreThread(EntityInterface $entity, $field_name, $mode, $comments_per_page, $comment_id = 0);

}
