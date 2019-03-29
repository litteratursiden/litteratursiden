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
    return \Drupal::formBuilder()->getForm('Drupal\lit_search\Form\SearchAutocompleteForm');
  }

}
