<?php

namespace Drupal\lit_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\Bundle as BaseBundle;

/**
 * Filter class which allows filtering by entity bundles.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("lit_bundle")
 */
class Bundle extends BaseBundle {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions) && $this->options['exposed'] == TRUE) {
      $user = \Drupal::currentUser();
      $types = $this->bundleInfoService->getBundleInfo($this->entityTypeId);
      $this->valueTitle = $this->t('@entity types', ['@entity' => $this->entityType->getLabel()]);

      $options = [];
      foreach ($types as $type => $info) {
        if ($user->hasPermission("create $type content")) {
          $options[$type] = $info['label'];
        }
      }

      asort($options);
      $this->valueOptions = $options;
    }
    else {
      $this->valueOptions = parent::getValueOptions();
    }

    return $this->valueOptions;
  }

}
