<?php

namespace Drupal\lit_comment_extra\Plugin\Field\FieldFormatter;

use Drupal\comment\Plugin\Field\FieldFormatter\CommentDefaultFormatter;
use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\lit_comment_extra\Controller\CommentLoadMoreController;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lit_comment_extra\CommentStorageExtraInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default comment formatter.
 *
 * @FieldFormatter(
 *   id = "comment_load_more",
 *   module = "lit_fields",
 *   label = @Translation("Comment list (load more)"),
 *   field_types = {
 *     "comment"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class CommentLoadMoreFormatter extends CommentDefaultFormatter {

  /**
   * Comment manager service.
   *
   * @var \Drupal\comment\CommentManagerInterface
   */
  public $commentManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $formatter = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $formatter->commentManager = $container->get('comment.manager');
    return $formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $default_settings = parent::defaultSettings();
    unset($default_settings['pager_id']);

    return $default_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    unset($element['pager_id']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (!($this->storage instanceof CommentStorageExtraInterface)) {
      return [];
    }

    $elements = [];
    $output = [];

    $field_name = $this->fieldDefinition->getName();
    $entity = $items->getEntity();

    $status = $items->status;

    if ($status != CommentItemInterface::HIDDEN && empty($entity->in_preview) &&
      // Comments are added to the search results and search index by
      // comment_node_update_index() instead of by this formatter, so don't
      // return anything if the view mode is search_index or search_result.
      !in_array($this->viewMode, ['search_result', 'search_index'])) {
      $comment_settings = $this->getFieldSettings();

      // Only attempt to render comments if the entity has visible comments.
      // Unpublished comments are not included in
      // $entity->get($field_name)->comment_count, but unpublished comments
      // should display if the user is an administrator.
      $elements['#cache']['contexts'][] = 'user.permissions';
      if ($this->currentUser->hasPermission('access comments') || $this->currentUser->hasPermission('administer comments')) {
        $output['comments'] = [
          '#type' => 'container',
          '#attributes' => [
            'id' => "comments-container-{$entity->id()}",
            'class' => ['comments-container'],
          ],
        ];

        if ($entity->get($field_name)->comment_count || $this->currentUser->hasPermission('administer comments')) {
          $output['comments'] += CommentLoadMoreController::buildComments($entity, $field_name, $this->getSetting('view_mode'));
        }
      }

      // Append comment form if the comments are open and the form is set to
      // display below the entity. Do not show the form for the print view mode.
      if ($status == CommentItemInterface::OPEN && $comment_settings['form_location'] == CommentItemInterface::FORM_BELOW && $this->viewMode != 'print') {
        // Only show the add comment form if the user has permission.
        $elements['#cache']['contexts'][] = 'user.roles';
        if ($this->currentUser->hasPermission('post comments')) {
          $output['comment_form'] = [
            '#lazy_builder' => [
              'comment.lazy_builders:renderForm',
              [
                $entity->getEntityTypeId(),
                $entity->id(),
                $field_name,
                $this->getFieldSetting('comment_type'),
              ],
            ],
            '#create_placeholder' => TRUE,
          ];
        }
        else {
          $output['comment_form'] = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['comments-posting-forbidden'],
            ],
            '#markup' => $this->commentManager->forbiddenMessage($entity, $field_name),
          ];
        }
      }

      $elements[] = $output + [
        '#comment_type' => $this->getFieldSetting('comment_type'),
        '#comment_display_mode' => $this->getFieldSetting('default_mode'),
        'comments' => [],
        'comment_form' => [],
      ];
    }

    return $elements;
  }

}
