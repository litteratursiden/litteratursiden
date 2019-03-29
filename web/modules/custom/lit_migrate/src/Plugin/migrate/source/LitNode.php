<?php

namespace Drupal\lit_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d6\Node;

/**
 * @MigrateSource(
 *   id = "lit_node"
 * )
 */
class LitNode extends Node {

  /**
   * @inheritdoc
   */
  protected function getFieldValues(Row $node) {
    $values = [];
    foreach ($this->getFieldInfo($node->getSourceProperty('type')) as $field => $info) {
      $cck_data = $this->getCckData($info, $node);

      // Add alt and title to image fields.
      if ($info['type'] == 'filefield' && $info['widget_module'] == 'imagefield') {
        // Field may have multiple values.
        foreach ($cck_data as $i => $data) {
          $image_data = unserialize($data['data']);

          $cck_data[$i]['alt'] = $image_data['alt'] ?? '';
          $cck_data[$i]['title'] = $image_data['title'] ?? '';
        }
      }

      // Add protocol to link.
      if ($info['type'] == 'link') {
        // Field may have multiple values.
        foreach ($cck_data as $i => $data) {
          $url = $data['url'];

          if (strpos($url, 'www') !== FALSE && strpos($url, '://') === FALSE) {
            $cck_data[$i]['url'] = 'http://' . $url;
          }

        }
      }

      $values[$field] = $cck_data;
    }
    return $values;
  }

}
