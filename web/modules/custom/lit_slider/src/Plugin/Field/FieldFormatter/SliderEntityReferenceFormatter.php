<?php

namespace Drupal\lit_slider\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;

/**
 * Plugin implementation of the 'Slider Entity Reference' formatter.
 *
 * @FieldFormatter(
 *   id = "slider_entity_reference",
 *   label = @Translation("Slider Entity Reference"),
 *   description = @Translation("Display an entity reference as a slider."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class SliderEntityReferenceFormatter extends EntityReferenceEntityFormatter {

  /**
   * @inheritdoc
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    foreach ($elements as $delta => $element) {
      $elements[$delta]['#attributes']['class'][] = 'lit-slider-item-content';
    }
    $elements['#attributes']['class'][] = 'lit-slider';

    $elements['#attached']['library'][] = 'lit_slider/lit_slider';

    return $elements;
  }

}
