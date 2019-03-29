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
 *   id = "add_entity_bundle_type",
 *   label = @Translation("Entity bundle's type field"),
 *   description = @Translation("Adds the entity bundle's type to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddEntityBundleType extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Type'),
        'description' => $this->t('Entity bundle type'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['lit_search_api_entity_bundle_type'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();

    if ($bundle = $entity->bundle()) {
      $bundle_type_id = $entity->getEntityType()->getBundleEntityType();
      $bundle_label = \Drupal::entityTypeManager()
        ->getStorage($bundle_type_id)
        ->load($bundle)
        ->label();

      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'lit_search_api_entity_bundle_type');

      foreach ($fields as $field) {
        $field->addValue($bundle_label);
      }
    }
  }

}
