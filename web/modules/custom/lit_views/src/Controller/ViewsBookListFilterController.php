<?php

namespace Drupal\lit_views\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides controller for views book filter area plugin.
 */
class ViewsBookListFilterController extends ControllerBase {

  /**
   * Ajax callback for updating views with book list filter.
   */
  public function updateBookListView($js, $view_id, $view_display_id, $book_list_id) {
    $ajax_response = new AjaxResponse();

    if ($js != 'ajax') {
      // Redirect user to home page in case if it's not an ajax request.
      return new RedirectResponse('/');
    }

    $view = Views::getView($view_id);
    if (is_object($view)) {
      $args = [$book_list_id];

      $view->setArguments($args);
      $view->setDisplay($view_display_id);
      $view->preExecute();
      $view->execute();

      $view_content = $view->preview($view_display_id);
      $views_selector = ".view-book-list-filter-$view_id-$view_display_id .form-group > div";
      $ajax_response->addCommand(new ReplaceCommand($views_selector, $view_content));
    }

    return $ajax_response;
  }

  /**
   * Access callback for updating book list view.
   */
  public function updateBookListViewAccess($view_id, $view_display_id) {
    // We want to make sure that user has access to the view.
    $view = Views::getView($view_id);
    return AccessResult::allowedIf($view->access([$view_display_id]));
  }

}
