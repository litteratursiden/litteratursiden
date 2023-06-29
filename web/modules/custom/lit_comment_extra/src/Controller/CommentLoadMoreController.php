<?php

namespace Drupal\lit_comment_extra\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines a controller for comments load more formatter.
 */
class CommentLoadMoreController extends ControllerBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('current_user'));
  }

  /**
   * Access callback for loading comments.
   *
   * @param string $entity_type
   *   Entity type to which comments are belong to.
   * @param int $entity
   *   ID of the entity to which comments are belong to.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Whether user has access.
   */
  public function ajaxLoadMoreAccess($entity_type, $entity, $field_name) {
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    if (!$storage) {
      return AccessResult::forbidden('The entity type is invalid.');
    }

    $user_access = $this->currentUser->hasPermission('access comments') || $this->currentUser->hasPermission('administer comments');
    $entity = $storage->load($entity);
    return AccessResult::allowedIf($user_access && $entity && $entity->access('view'));
  }

  /**
   * Loads more comments via ajax.
   *
   * @param string $js
   *   Defines whether the request is 'ajax' or 'nojs'.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose comment(s) needs rendering.
   * @param string $field_name
   *   The field_name whose comment(s) needs rendering.
   * @param string $view_mode
   *   Comments view mode.
   * @param int $last_comment_id
   *   Comment ID after which other comments should be loaded.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Ajax response.
   */
  public function ajaxLoadMore($js, EntityInterface $entity, $field_name, $view_mode, $last_comment_id) {
    if ($js != 'ajax') {
      // Redirect user to home page in case if it's not an ajax request.
      return new RedirectResponse('/');
    }

    $ajax_response = new AjaxResponse();
    $comments = static::buildComments($entity, $field_name, $view_mode, $last_comment_id);

    $load_more = FALSE;
    if (isset($comments['load_more'])) {
      $load_more = $comments['load_more'];
      unset($comments['load_more']);
    }

    $comments_selector = "#comments-container-{$entity->id()}";
    $ajax_response->addCommand(new ReplaceCommand("$comments_selector .comment-load-more", $comments));

    if ($load_more) {
      $ajax_response->addCommand(new AppendCommand("$comments_selector", $load_more));
    }
    return $ajax_response;
  }

  /**
   * Builds renderable array for comments with "load more" button.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose comment(s) needs rendering.
   * @param string $field_name
   *   The field_name whose comment(s) needs rendering.
   * @param string $view_mode
   *   Comments view mode.
   * @param int $last_comment_id
   *   Comment ID after which other comments should be loaded.
   *
   * @return array
   *   Renderable array ready for displaying.
   */
  public static function buildComments(EntityInterface $entity, $field_name, $view_mode, $last_comment_id = 0) {
    $build = [];

    $comment_field = $entity->get($field_name);
    $mode = $comment_field->getSetting('default_mode');
    $comments_per_page = $comment_field->getSetting('per_page');

    $entity_type_manager = \Drupal::entityTypeManager();

    /** @var \Drupal\lit_comment_extra\CommentStorageExtraInterface $storage */
    $storage = $entity_type_manager->getStorage('comment');
    $view_builder = $entity_type_manager->getViewBuilder('comment');

    $comments = $storage->loadMoreThread($entity, $field_name, $mode, $comments_per_page, $last_comment_id);
    if ($comments) {
      $build = $view_builder->viewMultiple($comments, $view_mode);

      if (count($comments) == $comments_per_page) {
        /** @var \Drupal\comment\CommentInterface $last_comment */
        $last_comment = end($comments);
        $route_params = [
          'js' => 'nojs',
          'entity_type' => $entity->getEntityTypeId(),
          'entity' => $entity->id(),
          'field_name' => $field_name,
          'view_mode' => $view_mode,
          'last_comment_id' => $last_comment->id(),
        ];

        $build['load_more'] = [
          '#type' => 'link',
          '#title' => t('Load more'),
          '#url' => Url::fromRoute('lit_comment_extra.load_more', $route_params),
          '#attributes' => [
            'class' => ['comment-load-more', 'use-ajax'],
          ],
          '#attached' => [
            'library' => ['core/drupal.ajax'],
          ],
        ];
      }
    }

    return $build;
  }

}
