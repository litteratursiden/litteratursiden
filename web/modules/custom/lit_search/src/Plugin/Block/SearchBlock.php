<?php

namespace Drupal\lit_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a search block.
 *
 * @Block(
 *   id = "lit_search_block",
 *   admin_label = @Translation("Search block"),
 * )
 */
class SearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\lit_search\Form\SearchForm');

    // Drupal 11 does not move form attributes to the block wrapper. The theme
    // CSS expects the form classes on the wrapper, so move them.
    $form['#wrapper_attributes'] = $form['#attributes'] ?? [];
    unset($form['#attributes']);

    return $form;
  }

}
