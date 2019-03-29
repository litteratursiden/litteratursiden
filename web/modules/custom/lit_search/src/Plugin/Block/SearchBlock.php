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
    return \Drupal::formBuilder()->getForm('Drupal\lit_search\Form\SearchForm');
  }

}
