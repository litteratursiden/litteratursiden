<?php

namespace Drupal\lit_open_platform\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\lit_open_platform\Api\SearchClient as Client;
use Drupal\lit_open_platform\Transformers\SearchTransformer;
use Drupal\node\Plugin\EntityReferenceSelection\NodeSelection;

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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $module_handler, $current_user, $entity_field_manager, $entity_type_bundle_info, $entity_repository);

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

    // Search locally.
    $query = $this->buildEntityQuery($match, $match_operator);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    // If no local results - only return results from the open platform.
    if (empty($result)) {
      return $this->processEntities([], $books);
    }

    $entities = $this->entityTypeManager->getStorage($target_type)->loadMultiple($result);

    return $this->processEntities($entities, $books);
  }

  /**
   * Process entities to union drupal results with the open platform results.
   *
   * @param array $entities
   *   A list of entities.
   * @param array $books
   *   A list of books.
   *
   * @return array
   *   A list of options.
   */
  private function processEntities(array $entities, array $books): array {
    $entity_labels = [];
    $options = [];

    $book_default_image = NULL;

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
          $file = File::load($fid);
          if (!is_null($file)) {
            $uri = $file->getFileUri();
            $theme['#image'] = ImageStyle::load('search_autocomplete')->buildUrl($uri);
          }
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

        $options[$bundle][$entity_id] = \Drupal::service('renderer')->render($theme);
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
          '#storage' => t('Open platform'),
        ];

        $options['book'][$book['pid']] = \Drupal::service('renderer')->render($theme);
      }
    }

    if (empty($options['book'] || is_null($options['book']))) {
      return [];
    }

    $options['book'] = $this->sortBooks($options['book'], $entity_labels);

    return $options;
  }

  /**
   * Sort books.
   *
   * @param array $books
   *   A list of books.
   * @param array $titles
   *   A list of titles.
   *
   * @return array
   *   A list of sorted books.
   */
  private function sortBooks(array $books, array $titles): array {
    $result = [];

    asort($titles, SORT_NATURAL);

    $max = 10;
    $i = 1;
    foreach ($titles as $id => $title) {
      if (isset($books[$id])) {
        $result[$id] = $books[$id];

        if ($i > $max) {
          break;
        }
        $i++;
      }
    }

    return $result;
  }

}
