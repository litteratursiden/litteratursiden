<?php

namespace Drupal\lit_fields\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides controller for entity reference tabs field.
 */
class EntityReferenceTabsController extends ControllerBase {

  /**
   * Access callback for updating entity reference tabs.
   *
   * @param string $entity_type
   *   Entity type by which tabs will be updated.
   * @param int $entity
   *   ID of the entity by which entity tabs will be updated.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Whether user has access.
   */
  public function ajaxUpdateTabsAccess($entity_type, $entity) {
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    if (!$storage) {
      return AccessResult::forbidden('The entity type is invalid.');
    }

    $entity = $storage->load($entity);
    return AccessResult::allowedIf($entity && $entity->access('view'));
  }

  /**
   * Updates entity reference tabs via ajax.
   *
   * @param EntityInterface $entity
   *   Entity which should be shown on entity reference tabs.
   * @param string $mode
   *   Mode in which is needed to display entity.
   * @param string $ajax_wrapper_id
   *   ID if the tabs container.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function ajaxUpdateTabs(EntityInterface $entity, $mode, $ajax_wrapper_id) {
    $ajax_response = new AjaxResponse();

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
    $content = $view_builder->view($entity, $mode);

    $ajax_response->addCommand(new HtmlCommand("#$ajax_wrapper_id .entity-reference-tabs-content", $content));
    $ajax_response->addCommand(new InvokeCommand("#$ajax_wrapper_id .entity-reference-tabs li", 'removeClass', ['active']));
    $ajax_response->addCommand(new InvokeCommand("#$ajax_wrapper_id .entity-reference-tabs li.tab-{$entity->id()}", 'addClass', ['active']));
    return $ajax_response;
  }

}
