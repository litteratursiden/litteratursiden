<?php

namespace Drupal\lit_cover_service\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Litteratursiden Cover Service Integration routes.
 */
class LitCoverServiceController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
