<?php

namespace Drupal\lit\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for empty page.
 */
class EmptyPageController extends ControllerBase {

  /**
   * Generates empty content.
   */
  public function content() {
    return [];
  }

}
