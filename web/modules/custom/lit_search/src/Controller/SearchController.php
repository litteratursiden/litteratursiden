<?php

namespace Drupal\lit_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\lit_search\SearchGroup;
use Drupal\search_api\Entity\Index;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for SearchController.
 *
 * @package Drupal\lit_search\Controller
 */
class SearchController extends ControllerBase {

  /**
   * Search autocomplete view mode.
   */
  public const VIEW_MODE = 'search_autocomplete';

  /**
   * Solr index.
   *
   * @var \Drupal\Core\Entity\EntityInterface|null|static
   */
  protected $index;

  /**
   * SearchController constructor.
   */
  public function __construct() {
    $this->index = Index::load('content');
  }

  /**
   * Autocomplete search result.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A http request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A json response.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function autocomplete(Request $request) {
    $match = $request->query->get('q');
    $data = [];

    if ($match && strlen($match) >= 3) {
      // Check if cached result exists.
      $cid = 'lit_search_' . sha1($match);
      $data = \Drupal::cache()->get($cid);

      if (!$data) {
        // Build query.
        $query = $this->index->query();
        $query->keys($match);
        $results = $query->execute();

        // Get groups from result.
        $response = $results->getExtraData('elasticsearch_response', []);
        $groups = $response['aggregations']['groups']['buckets'] ?? [];

        // Get total number of result.
        $total = $response['hits']['total']['value'];

        // Create and theme response.
        $items = $this->transformResults($groups);
        $theme = [
          '#theme' => 'lit_search_autocomplete',
          '#match' => $match,
          '#books' => isset($items['book']) ? $items['book']->setTitle(t('Books')) : $this->createGroup(t('Books')),
          '#authors' => isset($items['authorportrait']) ? $items['authorportrait']->setTitle(t('Authors')) : $this->createGroup(t('Authors')),
          "#analyses" => isset($items['analysis']) ? $items['analysis']->setTitle(t('Analyses')) : $this->createGroup(t('Analyses')),
          "#articles" => isset($items['article']) ? $items['article']->setTitle(t('Articles')) : $this->createGroup(t('Articles')),
          "#blogs" => isset($items['blog']) ? $items['blog']->setTitle(t('Blogs')) : $this->createGroup(t('Blogs')),
          "#lists" => isset($items['booklist']) ? $items['booklist']->setTitle(t('Book lists')) : $this->createGroup(t('Book lists')),
          "#interviews" => isset($items['interview']) ? $items['interview']->setTitle(t('Interviews')) : $this->createGroup(t('Interviews')),
          "#reviews" => isset($items['review']) ? $items['review']->setTitle(t('Reviews')) : $this->createGroup(t('Reviews')),
          "#similars" => isset($items['similar']) ? $items['similar']->setTitle(t('Something similars')) : $this->createGroup(t('Something similars')),
          "#topics" => isset($items['topic']) ? $items['topic']->setTitle(t('Topics')) : $this->createGroup(t('Topics')),
        ];

        $data = [
          'total' => $total,
          'data' => \Drupal::service('renderer')->render($theme),
        ];

        \Drupal::cache()->set($cid, $data, \Drupal::time()->getRequestTime() + 300, ['search_autocomplete']);
      }
      else {
        $data = $data->data;
      }
    }

    return new JsonResponse($data);
  }

  /**
   * Change the data structure to match the one required to theme it.
   *
   * @param array $groups
   *   A list of groups.
   *
   * @return array
   *   A list of groups keyed by group key.
   */
  private function transformResults(array $groups): array {
    $result = [];

    foreach ($groups as $group) {
      $count = $group['doc_count'] ?? 0;
      $items = $this->findEntitiesFromGroupItems($group['entities']['hits']['hits']);
      $result[$group['key']] = $this->createGroup($group['key'], $count, $items);
    }

    return $result;
  }

  /**
   * Find entities from group items.
   *
   * @param array $hits
   *   A list of hits.
   *
   * @return array
   *   A list of matched rendered entities.
   */
  private function findEntitiesFromGroupItems(array $hits): array {
    $result = [];

    foreach ($hits as $hit) {
      // Get entity type and id form the elastic search id.
      preg_match('/(\w*):(\w*)\/(\d*):(\w*)/', $hit['_id'], $matches);

      try {
        $entity = \Drupal::entityTypeManager()->getStorage($matches[2])->load($matches[3]);
        $result[] = $this->renderEntity($entity);
        ;
      }
      catch (\Exception $e) {
        // No action a item simply was not loaded.
      }
    }

    return $result;
  }

  /**
   * Render entity with given view mode.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   *
   * @return mixed|null
   *   A rendered entity.
   */
  private function renderEntity(EntityInterface $entity): mixed {
    $render_controller = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
    $pre_render = $render_controller->view($entity, self::VIEW_MODE);

    return \Drupal::service('renderer')->render($pre_render);
  }

  /**
   * Create groups.
   *
   * @param string $machine_name
   *   A search group machine name.
   * @param int $count
   *   The count.
   * @param array $items
   *   A list of items.
   *
   * @return \Drupal\lit_search\SearchGroup
   *   A solr search group.
   */
  private function createGroup(string $machine_name, int $count = 0, array $items = []): SearchGroup {
    $solrGroup = new SearchGroup($machine_name);
    $solrGroup
      ->setCount($count)
      ->setItems($items);

    return $solrGroup;
  }

}
