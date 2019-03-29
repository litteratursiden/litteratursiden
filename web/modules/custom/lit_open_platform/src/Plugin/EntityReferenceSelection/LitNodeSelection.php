<?php

namespace Drupal\lit_open_platform\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\lit_open_platform\Api\SearchClient as Client;
use Drupal\lit_open_platform\Transformers\SearchTransformer;
use \Drupal\node\Plugin\EntityReferenceSelection\NodeSelection;

/**
 * Provides specific access control for the node entity type.
 *
 * @EntityReferenceSelection(
 *   id = "lit:node",
 *   label = @Translation("Node selection"),
 *   entity_types = {"node"},
 *   group = "default",
 *   weight = 1
 * )
 */
class LitNodeSelection extends NodeSelection {

  /**
   * The client instance.
   *
   * @var \Drupal\lit_open_platform\Api\SearchClient
   */
  private $client;

  /**
   * @inheritdoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $module_handler, $current_user);

    // Get module config.
    $config = \Drupal::config('lit_open_platform.settings');

    // Get the open platform API client.
    $client_id = $config->get('client_id') ?? '';
    $client_secret = $config->get('client_secret') ?? '';
    $this->client = Client::getInstance($client_id, $client_secret);
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $target_type = $this->getConfiguration()['target_type'];

    // Search books on the open platform and transform they to drupal format.
    $books = SearchTransformer::transformCollection($this->client->requestSearch($match));

    // Search locally
    $query = $this->buildEntityQuery($match, $match_operator);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    // if no local results - only return results from the open platform
    if (empty($result)) {
        return $this->processEntities([], $books);
    }

    $entities = $this->entityManager->getStorage($target_type)->loadMultiple($result);

    return $this->processEntities($entities, $books);
  }

  /**
   * Process entities to union drupal results with the open platform results.
   *
   * @param array $entities
   * @param array $books
   * @return array
   */
  private function processEntities(array $entities, array $books): array {
    $entity_labels = [];
    $options = [];

    $book_default_image = null;

    // Process local books.
    foreach ($entities as $entity_id => $entity) {
      $bundle = $entity->bundle();
      $entity_labels[$entity_id] = trim($entity->label());
      if ($bundle == 'book') {
        // Custom view for a book.
        $theme = [
          '#theme' => 'lit_open_platform_book_selection',
          '#label' => $entity_labels[$entity_id],
          '#author' => $entity->get('field_book_author')->getString(),
        ];

        // Set image.
        $image = $entity->get('field_book_cover_image')->first();

        if ($image && $fid = $image->get('target_id')->getValue()) {
          $uri = File::load($fid)->getFileUri();
          $theme['#image'] = ImageStyle::load('search_autocomplete')->buildUrl($uri);
        }
        else {
          if ($book_default_image) {
            $theme['#image'] = $book_default_image;
          }
          else {
            $default_image = $entity->get('field_book_cover_image')->getSetting('default_image');
            if ($default_image && $default_image['uuid']) {
              $default = \Drupal::service('entity.repository')->loadEntityByUuid('file', $default_image['uuid']);
              if ($default) {
                $theme['#image'] = $book_default_image = ImageStyle::load('search_autocomplete')->buildUrl($default->getFileUri());
              }
            }
          }
        }

        $options[$bundle][$entity_id] = render($theme);
      }
      else {
        $options[$bundle][$entity_id] = $entity_labels[$entity_id];
      }
    }

    // Process the open platform books.
    foreach ($books as $book) {
      if (!in_array($book['title'], $entity_labels)) {
        $entity_labels[$book['pid']] = trim($book['title']);

        // Custom view for a book.
        $theme = [
          '#theme' => 'lit_open_platform_book_selection',
          '#label' => $book['title'],
          '#image' => $book['image'] ?: $book_default_image,
          '#author' => $book['author'],
          '#storage' => t('Open platform')
        ];

        $options['book'][$book['pid']] = render($theme);
      }
    }

    $options['book'] = $this->sortBooks($options['book'], $entity_labels);

    return $options;
  }

  /**
   * @param $books
   * @param $titles
   * @return array
   */
  private function sortBooks($books, $titles): array {
    $result = [];

    asort($titles, SORT_NATURAL);

    $max = 10; $i = 1;
    foreach ($titles as $id => $title) {
      if (isset($books[$id])) {
        $result[$id] = $books[$id];

        if ($i > $max) break;
        $i++;
      }
    }

    return $result;
  }

}
