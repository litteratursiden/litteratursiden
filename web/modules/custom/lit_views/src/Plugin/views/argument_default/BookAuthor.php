<?php

namespace Drupal\lit_views\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default argument plugin to extract a book author node ID.
 *
 * @ViewsArgumentDefault(
 *   id = "book_author",
 *   title = @Translation("Book author node ID from URL")
 * )
 */
class BookAuthor extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new BookAuthor instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    $node = $this->routeMatch->getParameter('node');
    if (empty($node) || !($node instanceof NodeInterface)) {
      return NULL;
    }

    $node_type = $node->getType();
    switch ($node_type) {
      case 'author_portrait':
        return $node->id();

      case 'book':
        $author_field = $node->get('field_book_reference_author');
        return empty($author_field->target_id) ? NULL : $author_field->target_id;

      case 'interview':
        $author_field = $node->get('field_interview_reference_author');
        return empty($author_field->target_id) ? NULL : $author_field->target_id;

      case 'review':
        $book_field = $node->get('field_review_reference_book');
        $author_field = isset($book_field->entity) ? $book_field->entity->get('field_book_reference_author') : NULL;
        return empty($author_field->target_id) ? NULL : $author_field->target_id;

      case 'analysis':
        $book_field = $node->get('field_analysis_reference_book');
        $author_field = isset($book_field->entity) ? $book_field->entity->get('field_book_reference_author') : NULL;
        return empty($author_field->target_id) ? NULL : $author_field->target_id;

      case 'article':
      case 'topic':
        $internal_links = $node->get("field_{$node_type}_internal_link")->getValue();
        $node_ids = [];
        foreach ($internal_links as $value) {
          if (isset($value['target_id'])) {
            $node_ids[] = $value['target_id'];
          }
        }

        $author_ids = [];
        if ($node_ids) {
          $author_ids = \Drupal::entityQuery('node')
            ->condition('type', 'author_portrait')
            ->condition('nid', $node_ids, 'IN')
            ->range(0, 1)
            ->execute();
        }

        return empty($author_ids) ? NULL : reset($author_ids);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['url'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

}
