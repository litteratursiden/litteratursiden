<?php

namespace Drupal\lit_search\EventSubscriber;


use Drupal\elasticsearch_connector\Event\BuildSearchParamsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SearchEventsSubscriber
 *
 * @package Drupal\lit_search\EventSubscriber
 */
class SearchEventsSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    return [
      BuildSearchParamsEvent::BUILD_QUERY => 'buildQuery',
    ];
  }

  /**
   * Extend elastic search query with aggregations to create groups.
   *
   * @param \Drupal\elasticsearch_connector\Event\BuildSearchParamsEvent $event
   */
  public function buildQuery(BuildSearchParamsEvent $event) {
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
                      'title'
                    ]
                  ],
                  'size' => 3
                ]
              ]
            ]
          ],
        ],
      ],
    ];

    $event_params = $event->getElasticSearchParams();
    $params = array_replace_recursive($event_params, $params);
    $event->setElasticSearchParams($params);
  }
}