<?php

namespace Drupal\lit_search\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the entity label to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "add_entity_label",
 *   label = @Translation("Entity label field"),
 *   description = @Translation("Adds the entity label to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddEntityLabel extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Label'),
        'description' => $this->t('Entity label'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['lit_search_api_entity_label'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();

    if ($label = $entity->label()) {
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'lit_search_api_entity_label');

      foreach ($fields as $field) {
        $field->addValue($label);
      }
    }
  }

}
