<?php

namespace Drupal\lit;

use Drupal\Core\Security\TrustedCallbackInterface;

class FacetTypePrerender implements TrustedCallbackInterface {

  public static function trustedCallbacks() {
    return ['preRender'];
  }

  /**
   * #pre_render callback: Sets color preset logo.
   */
  public static function preRender($build) {
    if (!isset($build['content'])) {
      return $build;
    }

    $items = $build['content'][0]['#items'];

    $sorted = [];
    foreach ($items as $i => $item) {
      switch ($item['#title']['#value']) {
        case 'book':
          $item['#title']['#value'] = t('Books');
          $sorted[0] = $item;
          unset($items[$i]);
          break;

        case 'author_portrait':
          $item['#title']['#value'] = t('Author portraits');
          $sorted[1] = $item;
          unset($items[$i]);
          break;

        case 'topic':
          $item['#title']['#value'] = t('Topics');
          $sorted[2] = $item;
          unset($items[$i]);
          break;

        case 'article':
          $item['#title']['#value'] = t('Articles');
          $sorted[3] = $item;
          unset($items[$i]);
          break;

        case 'blog':
          $item['#title']['#value'] = t('Blogs');
          $sorted[4] = $item;
          unset($items[$i]);
          break;

        case 'book_list':
          $item['#title']['#value'] = t('Book lists');
          $sorted[5] = $item;
          unset($items[$i]);
          break;

        default:
          break;
      }
    }

    ksort($sorted);
    $build['content'][0]['#items'] = array_merge($sorted, $items);
    $build['content'][0]['#prefix'] = '<div class="facets-widget-checkbox">';
    $build['content'][0]['#suffix'] = '</div>';

    return $build;
  }
}
