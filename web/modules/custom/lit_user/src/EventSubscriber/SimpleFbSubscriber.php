<?php

namespace Drupal\lit_user\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Drupal\simple_fb_connect\SimpleFbConnectFbFactory;


/**
 * Listens to the simple fb connect.
 */
class SimpleFbSubscriber implements EventSubscriberInterface
{
    protected $facebook;
    protected $persistentDataHandler;

    /**
     * Constructor.
     *
     * We use dependency injection get SimpleFbConnectFbFactory.
     *
     * @param SimpleFbConnectFbFactory $fb_factory
     *   For getting Facebook and SimpleFbConnectPersistentDataHandler services.
     */
    public function __construct(SimpleFbConnectFbFactory $fb_factory) {
        $this->facebook = $fb_factory->getFbService();
        $this->persistentDataHandler = $fb_factory->getPersistentDataHandler();
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     *   The event names to listen to
     */
    static function getSubscribedEvents() {
        $events = [];
        $events['simple_fb_connect.user_created'][] = ['userCreated'];
        return $events;
    }

    /**
     * Reacts to the event when new user is created via Simple FB Connect.
     *
     */
    public function userCreated(GenericEvent $event) {
        $user = $event->getArgument('account');
        $user->set('field_user_full_name', $event->getArgument('name'));
    }
}
