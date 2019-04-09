<?php

namespace Drupal\lit_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\facets\Exception\Exception;
use Drupal\lit_search\SearchGroup;
use Drupal\search_api\Entity\Index;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class SearchController.
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
   * @param Request $request
   * @return string
   */
  public function autocomplete(Request $request) {
    $match = $request->query->get('q');

    $total = 0;
    $data = '';

    if ($match && strlen($match) >= 3) {
      // Build query.
      $query = $this->index->query();
      $query->keys($match);
      $results = $query->execute();

      // Get groups from result.
      $response = $results->getExtraData('elasticsearch_response', []);
      $groups = $response['aggregations']['groups']['buckets'] ?? [];

      // Get total number of result.
      $total = $response['hits']['total'];

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
      $data = render($theme);
   }

    return new JsonResponse([
      'total' => $total,
      'data' => $data,
    ]);
  }

  /**
   * Change the data structure to match the one required to theme it.
   *
   * @param array $groups
   *
   * @return array
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
   *
   *
   * @param array $hits
   *
   * @return array
   */
  private function findEntitiesFromGroupItems(array $hits): array {
    $result = [];

    foreach ($hits as $hit) {
      // Get entity type and id form the elastic search id.
      preg_match('/(\w*):(\w*)\/(\d*):(\w*)/', $hit['_id'], $matches);

      try {
        $entity = \Drupal::entityTypeManager()->getStorage($matches[2])->load($matches[3]);
        $result[] = $this->renderEntity($entity, self::VIEW_MODE);;
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
   * @param $entity
   * @param string $view_mode
   *
   * @return mixed|null
   */
  private function renderEntity($entity, string $view_mode) {
    $render_controller = \Drupal::entityManager()->getViewBuilder($entity->getEntityTypeId());
    $pre_render = $render_controller->view($entity, $view_mode);

    return render($pre_render);
  }

  /**
   * Create groups.
   *
   * @param string $machine_name
   * @param int $count
   * @param array $items
   *
   * @return \Drupal\lit_search\SearchGroup
   */
  private function createGroup(string $machine_name, int $count = 0, array $items = []): SearchGroup {
    $solrGroup = new SearchGroup($machine_name);
    $solrGroup
      ->setCount($count)
      ->setItems($items);

    return $solrGroup;
  }

}
