<?php

namespace Drupal\lit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller for authors.
 */
class AuthorsController extends ControllerBase {

  /**
   * Redirect path when visiting /authors
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response.
   */
  public function redirectPath() {
    $path = Url::fromUserInput('/forfattere')->getRouteName();
    $parameters = Url::fromUserInput('/forfattere')->getRouteParameters();
    return $this->redirect($path, $parameters);
  }
}
