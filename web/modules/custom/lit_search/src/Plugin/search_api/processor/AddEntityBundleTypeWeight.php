<?php

namespace Drupal\lit_search\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds the entity bundle's type weight to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "add_entity_bundle_type_weight",
 *   label = @Translation("Entity bundle's type weight"),
 *   description = @Translation("Adds the entity bundle's type weight to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class AddEntityBundleTypeWeight extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Type weight'),
        'description' => $this->t('Entity bundle type weight'),
        'type' => 'integer',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['lit_search_api_entity_bundle_type_weight'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();

    if ($bundle = $entity->bundle()) {
      $bundle_type_weight = 0;

      switch ($bundle) {
        case "book":
          $bundle_type_weight = 100;
          break;

        case "author_portrait":
          $bundle_type_weight = 90;
          break;

        case "topic":
          $bundle_type_weight = 80;
          break;

        case "article":
          $bundle_type_weight = 70;
          break;

        case "blog":
          $bundle_type_weight = 60;
          break;

        case "book_list":
          $bundle_type_weight = 50;
          break;

        default:
          break;
      }

      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'lit_search_api_entity_bundle_type_weight');

      foreach ($fields as $field) {
        $field->addValue($bundle_type_weight);
      }
    }
  }

}
