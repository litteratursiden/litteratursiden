<?php

namespace Drupal\lit\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for response.
 */
class ResponseSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::RESPONSE][] = ['improveResponse', -10];
    return $events;
  }

  /**
   * Improve response event listener.
   */
  public function improveResponse(ResponseEvent $event): void {
    $user = \Drupal::currentUser();

    $response = $event->getResponse();
    $response->headers->set('X-User-Role', $user->getRoles());

    $response->headers->setCookie(new Cookie('user-role', implode(',', $user->getRoles())));
    $response->headers->setCookie(new Cookie('user-id', $user->id()));
  }

}
