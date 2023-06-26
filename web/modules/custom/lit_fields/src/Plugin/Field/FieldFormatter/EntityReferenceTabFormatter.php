<?php

namespace Drupal\lit_fields\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'entity_reference_tab_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_tab_formatter",
 *   label = @Translation("Tab formatter"),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions"
 *   }
 * )
 */
class EntityReferenceTabFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'mode' => 'full',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['mode'] = array(
      '#type' => 'select',
      '#options' => $this->getEntityViewModes(),
      '#title' => $this->t('Select an entity view mode.'),
      '#default_value' => $this->getSetting('mode'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $available_view_modes = $this->getEntityViewModes();
    $view_mode = $this->getSetting('mode');

    $summary[] = $this->t('Entity view mode: @mode', ['@mode' => $available_view_modes[$view_mode]]);
    return $summary;
  }

  /**
   * Gets an array of target entity view modes.
   *
   * @return array
   *   Entity modes keyed by ID and valued by title.
   */
  protected function getEntityViewModes() {
    $field_settings = $this->getFieldSettings();
    $entity_type = $field_settings['target_type'];

    $view_mode_ids = \Drupal::entityQuery('entity_view_mode')
      ->accessCheck('FALSE')
      ->condition('targetEntityType', $entity_type)
      ->execute();

    $view_modes = [];
    /** @var \Drupal\Core\Entity\EntityViewModeInterface $view_mode */
    foreach (EntityViewMode::loadMultiple($view_mode_ids) as $view_mode) {
      $view_mode_name = str_replace("$entity_type.", '', $view_mode->id());
      $view_modes[$view_mode_name] = $view_mode->label();
    }

    return $view_modes;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $ajax_wrapper_id = Html::getUniqueId('entity-reference-tabs');
    $entity_mode = $this->getSetting('mode');

    $element = [
      '#theme' => 'entity_reference_tabs',
      '#id' => $ajax_wrapper_id,
      '#attributes' => [
        'class' => ['entity-reference-tabs-container'],
      ],
      '#attached' => [
        'library' => ['core/drupal.ajax'],
      ],
    ];

    $element['tabs'] = [
      '#theme' => 'item_list',
      '#items' => [],
      '#attributes' => [
        'class' => ['entity-reference-tabs'],
      ],
    ];

    $default_route_params = [
      'mode' => $entity_mode,
      'ajax_wrapper_id' => $ajax_wrapper_id,
    ];

    if (count($items) > 1) {
      foreach ($items as $item) {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        if ($entity = $item->entity) {
          $route_params = $default_route_params + [
              'entity_type' => $entity->getEntityTypeId(),
              'entity' => $entity->id(),
            ];

          $element['tabs']['#items'][] = [
            '#type' => 'link',
            '#title' => $entity->label(),
            '#url' => Url::fromRoute('lit_fields.render_entity_reference_tabs',
              $route_params),
            '#attributes' => [
              'class' => ['use-ajax'],
            ],
            '#wrapper_attributes' => [
              'class' => ['tab', "tab-{$entity->id()}"],
            ],
          ];
        }
      }
    }

    $element['content'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['entity-reference-tabs-content'],
      ],
    ];

    // By default the first tab is active.
    $element['tabs']['#items'][0]['#wrapper_attributes']['class'][] = 'active';


    if ($first = $items->first()) {
      $entity = $first->entity;

      if ($entity) {
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
        $element['content']['entity'] = $view_builder->view($entity, $entity_mode);
      }
    }

    return $element;
  }

}
