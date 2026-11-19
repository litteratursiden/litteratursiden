<?php

namespace Drupal\lit_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a search autocomplete block.
 *
 * @Block(
 *   id = "lit_search_autocomplete_block",
 *   admin_label = @Translation("Search autocomplete block"),
 * )
 */
class SearchAutocompleteBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\lit_search\Form\SearchAutocompleteForm');

    // Drupal 11 does not move form attributes to the block wrapper. The theme
    // CSS expects the form classes on the wrapper, so move them.
    $form['#wrapper_attributes'] = $form['#attributes'] ?? [];
    unset($form['#attributes']);

    return $form;
  }

}
