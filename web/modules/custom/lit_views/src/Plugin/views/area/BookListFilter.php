<?php

namespace Drupal\lit_views\Plugin\views\area;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Provides an area handler which filters books by book lists.
 *
 * @ViewsArea("book_list_filter")
 */
class BookListFilter extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    $content['book_lists'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['book-lists-container'],
      ],
      '#attached' => [
        'library' => ['core/drupal.ajax'],
      ],
    ];

    $book_lists = $this->getBookLists();
    $url_params = [
      'js' => 'nojs',
      'view_id' => $this->view->id(),
      'view_display_id' => $this->view->current_display,
    ];

    $current_book_list_id = $this->view->args[$this->options['contextual_filter']];

    foreach ($book_lists as $id => $title) {
      $content['book_lists'][$id] = [
        '#type' => 'link',
        '#title' => $title,
        '#url' => Url::fromRoute('lit_views.update_book_list_view', $url_params + ['book_list_id' => $id]),
        '#attributes' => [
          'class' => ['use-ajax', 'book-list-link'],
        ],
      ];

      if ($current_book_list_id == $id) {
        $content['book_lists'][$id]['#attributes']['class'][] = 'active';
      }
    }

    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(array $results) {
    $this->view->element['#attributes']['class'][] = 'view-book-list-filter-' . $this->view->id() . '-' . $this->view->current_display;
  }

  /**
   * {@inheritdoc}
   */
  public function preQuery() {
    $contextual_filter_index = $this->options['contextual_filter'];
    if (is_null($contextual_filter_index) || !empty($this->view->args[$contextual_filter_index])) {
      return;
    }

    $book_lists = $this->getBookLists();
    $book_list_id = key($book_lists);

    $this->view->args[$contextual_filter_index] = $book_list_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['contextual_filter'] = [
      'default' => NULL,
    ];

    $options['book_lists_number'] = [
      'default' => 5,
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['contextual_filter'] = [
      '#title' => $this->t('Book list contextual filter index'),
      '#description' => $this->t('Enter an index of contextual filter by book lists.'),
      '#type' => 'number',
      '#default_value' => $this->options['contextual_filter'],
      '#required' => TRUE,
    ];

    $form['book_lists_number'] = [
      '#title' => $this->t('Book lists number to display'),
      '#type' => 'number',
      '#default_value' => $this->options['book_lists_number'],
    ];
  }

  /**
   * Gets an array of book lists.
   *
   * @return array
   *    Array of book lists keyed by ID and valued by title.
   */
  protected function getBookLists() {
    $book_lists_number = $this->options['book_lists_number'];
    $cache_cid = "lit_views:book_lists:$book_lists_number";
    if ($cache = \Drupal::cache()->get($cache_cid)) {
      return $cache->data;
    }

    $book_list_ids = \Drupal::entityQuery('node')
      ->condition('type', 'book_list')
      ->condition('promote', Node::PROMOTED)
      ->sort('created')
      ->range(0, $book_lists_number)
      ->execute();

    /** @var Node[] $book_list_nodes */
    $book_list_nodes = Node::loadMultiple($book_list_ids);

    $book_lists = [];
    foreach ($book_list_nodes as $node) {
      $book_lists[$node->id()] = $node->getTitle();
    }

    \Drupal::cache()->set($cache_cid, $book_lists, Cache::PERMANENT, ['node_list']);
    return $book_lists;
  }

}
