<?php

namespace Drupal\lit_search\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the entity bundle's type to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "add_entity_bundle_type_machine",
 *   label = @Translation("Entity bundle's type machine name"),
 *   description = @Translation("Adds the entity bundle's type machine name to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddEntityBundleTypeMachine extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Type machine name'),
        'description' => $this->t('Entity bundle type machine name'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['lit_search_api_entity_bundle_type_machine_name'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();

    if ($bundle = $entity->bundle()) {
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'lit_search_api_entity_bundle_type_machine_name');

      foreach ($fields as $field) {
        $field->addValue($bundle);
      }
    }
  }

}
