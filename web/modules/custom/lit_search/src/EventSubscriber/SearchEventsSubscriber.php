<?php

namespace Drupal\lit_search\EventSubscriber;

use Drupal\elasticsearch_connector\Event\QueryParamsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * EventsSubscriber for search.
 *
 * @package Drupal\lit_search\EventSubscriber
 */
class SearchEventsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      QueryParamsEvent::class => 'buildQuery',
    ];
  }

  /**
   * Extend elastic search query with aggregations to create groups.
   *
   * @param \Drupal\elasticsearch_connector\Event\QueryParamsEvent $event
   *   The event to react on.
   */
  public function buildQuery(QueryParamsEvent $event) {
    $params = [
      'body' => [
        'aggs' => [
          'groups' => [
            'terms' => [
              'field' => 'lit_entity_bundle_type_machine_name',
            ],
            'aggs' => [
              'entities' => [
                'top_hits' => [
                  '_source' => [
                    'includes' => [
                      'title',
                    ],
                  ],
                  'size' => 3,
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $event->setParams(array_replace_recursive($event->getParams(), $params));
  }

}
