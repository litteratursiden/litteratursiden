<?php

namespace Drupal\lit_taxonomy\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 * @package Drupal\lit_taxonomy\Routing
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * @inheritdoc
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.taxonomy_vocabulary.overview_form')) {
      $route->setDefault('_form', 'Drupal\lit_taxonomy\Form\OverviewTerms');
    }
  }

}
