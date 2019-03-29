<?php

namespace Drupal\lit_notifications;

use Drupal\Core\Entity\Entity;
use Drupal\extrafield_views_integration\lib\ExtrafieldRenderClassInterface;

/**
 * Provides renderer for notification flag for integration with views.
 */
class FlagNotificationsExtraFieldRenderer implements ExtrafieldRenderClassInterface {

  /**
   * {@inheritdoc}
   */
  public static function render(Entity $entity) {
    /** @var \Drupal\flag\FlagServiceInterface $flag_service */
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('notifications');

    $build['flag_' . $flag->id()] = [
      '#lazy_builder' => [
        'flag.link_builder:build',
        [
          $entity->getEntityTypeId(),
          $entity->id(),
          $flag->id(),
        ],
      ],
      '#create_placeholder' => TRUE,
    ];

    return $build;
  }

}
