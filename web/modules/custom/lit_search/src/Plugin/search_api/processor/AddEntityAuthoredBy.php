<?php

namespace Drupal\lit_search\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the entity authored by to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "add_entity_authored_by",
 *   label = @Translation("Entity authored by field"),
 *   description = @Translation("Adds the entity authored by to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddEntityAuthoredBy extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Authored by'),
        'description' => $this->t('Authored by'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['lit_search_api_entity_authored_by'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();

    $user = $entity->uid->entity;

    $authored_by = $user->getDisplayName();

    if ($entity->label()) {
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'lit_search_api_entity_authored_by');

      foreach ($fields as $field) {
        $field->addValue($authored_by);
      }
    }
  }

}
