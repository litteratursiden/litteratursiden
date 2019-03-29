<?php

namespace Drupal\lit_slider\Plugin\views\style;

use Drupal\views\Plugin\views\style\DefaultStyle;

/**
 * Slider style plugin.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "lit_slider",
 *   title = @Translation("Slider"),
 *   help = @Translation("Display the results in a slider."),
 *   theme = "views_view_lit_slider",
 *   display_types = {"normal"}
 * )
 */
class SliderViews extends DefaultStyle {


}
