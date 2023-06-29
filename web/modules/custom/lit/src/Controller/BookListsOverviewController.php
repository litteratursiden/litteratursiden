<?php

namespace Drupal\lit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller for book lists overview page.
 */
class BookListsOverviewController extends ControllerBase {

  /**
   * Returns content.
   */
  public function content() {
    $query = \Drupal::database()->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('nfd.type', 'book_list')
      ->condition('nfd.status', 1)
      ->orderBy('title');

    $query->addExpression('SUBSTRING(TRIM(nfd.title), 1, 1)', 'firstchar');
    $query->addExpression('TRIM(nfd.title)', 'title');

    $result = $query->execute()->fetchAllAssoc('nid');

    $book_lists = $this->processBookLists($result);

    return [
      '#theme' => 'lit_book_lists_overview_page',
      '#book_lists' => $book_lists,
      '#cache' => [
        'tags' => ['lit:book_lists_overview_page'],
      ],
    ];
  }

  /**
   * @param array $book_lists
   * @return array
   */
  private function processBookLists(array $book_lists): array {
    $grouped = [];

    foreach ($book_lists as $nid => $book_list) {
      // $book_list->url = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $nid);
      $book_list->url = Url::fromRoute('entity.node.canonical', ['node' => $nid], ['absolute' => TRUE]);
      $grouped[strtoupper($book_list->firstchar)][] = $book_list;
    }

    return $grouped;
  }

}
