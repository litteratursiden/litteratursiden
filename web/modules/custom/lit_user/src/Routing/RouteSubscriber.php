<?php

namespace Drupal\lit_user\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase
{
    /**
     * {@inheritdoc}
     */
    protected function alterRoutes (RouteCollection $collection)
    {
        // Change controller for user_registrationpassword.confirm.
        if ($route = $collection->get('user_registrationpassword.confirm')) {
            $defaults = $route->setDefault('_controller' ,
                '\Drupal\lit_user\Controller\UserRegistrationPassword::confirmAccount');
        }
    }
}
