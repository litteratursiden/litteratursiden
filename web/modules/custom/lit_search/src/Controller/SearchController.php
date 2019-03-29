<?php

namespace Drupal\lit_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\lit_search\SolrGroup;
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
      $query->setOption('search_api_grouping', [
        'use_grouping' => TRUE,
        'fields' => [
          'lit_entity_bundle_type_machine_name',
        ],
        'group_limit' => 3,
      ]);
      $query = $query->execute();

      // Get groups from result.
      $response = $query->getExtraData('search_api_solr_response', []);
      $groups = $response['grouped']['sm_lit_entity_bundle_type_machine_name']['groups'] ?? [];

      // Get entities from result.
      $entities = $query->getResultItems();

      // Get total number of result.
      $total = $response['grouped']['sm_lit_entity_bundle_type_machine_name']['matches'];

      // Create and theme response.
      $items = $this->transformResults($groups, $entities);
      $theme = [
        '#theme' => 'lit_search_autocomplete',
        '#match' => $match,
        '#books' => isset($items['book']) ? $items['book']->setTitle(t('Books')) : $this->createGroup(t('Books')),
        '#authors' => isset($items['author_portrait']) ? $items['author_portrait']->setTitle(t('Authors')) : $this->createGroup(t('Authors')),
        "#analyses" => isset($items['analysis']) ? $items['analysis']->setTitle(t('Analyses')) : $this->createGroup(t('Analyses')),
        "#articles" => isset($items['article']) ? $items['article']->setTitle(t('Articles')) : $this->createGroup(t('Articles')),
        "#blogs" => isset($items['blog']) ? $items['blog']->setTitle(t('Blogs')) : $this->createGroup(t('Blogs')),
        "#lists" => isset($items['book_list']) ? $items['book_list']->setTitle(t('Book lists')) : $this->createGroup(t('Book lists')),
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
   * @param array $groups
   * @param array $entities
   * @return array
   */
  private function transformResults(array $groups, array $entities): array {
    $result = [];

    foreach ($groups as $group) {
      $count = $group['doclist']['numFound'] ?? 0;
      $items = $this->findEntitiesFromGroupItems($group['doclist']['docs'] ?? [], $entities);

      $result[$group['groupValue']] = $this->createGroup($group['groupValue'], $count, $items);
    }

    return $result;
  }

  /**
   * @param array $ids
   * @param array $entities
   * @return array
   */
  private function findEntitiesFromGroupItems(array $items, array $entities): array {
    $result = [];

    foreach ($items as $item) {
      if ($entity = $this->findEntityById($item['ss_search_api_id'], $entities)) {
        $result[] = $entity;
      }
    }

    return $result;
  }

  /**
   * @param string $id
   * @param array $entities
   * @return bool|mixed
   */
  private function findEntityById(string $id, array $entities) {
    if (isset($entities[$id])) {
      $entity = $entities[$id]->getOriginalObject()->getValue();

      return $this->renderEntity($entity, self::VIEW_MODE);
    }

    return FALSE;
  }

  /**
   * @param $entity
   * @param string $view_mode
   * @return mixed|null
   */
  private function renderEntity($entity, string $view_mode) {
    $render_controller = \Drupal::entityManager()->getViewBuilder($entity->getEntityTypeId());
    $pre_render = $render_controller->view($entity, $view_mode);

    return render($pre_render);
  }

  /**
   * @param string $machine_name
   * @param int $count
   * @param array $items
   * @return \Drupal\lit_search\SolrGroup
   */
  private function createGroup(string $machine_name, int $count = 0, array $items = []): SolrGroup {
    $solrGroup = new SolrGroup($machine_name);
    $solrGroup
      ->setCount($count)
      ->setItems($items);

    return $solrGroup;
  }

}
