<?php

namespace Drupal\lit_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "lit_author_portrait_node"
 * )
 */
class LitAuthorPortraitNode extends LitNode {

  /**
   * Fields to be avoided.
   */
  const AVOID_FIELDS = ['field_books'];

  /**
   * @inheritdoc
   */
  protected function getFieldValues(Row $node) {
    $values = [];
    foreach ($this->getFieldInfo($node->getSourceProperty('type')) as $field => $info) {
      // Avoid some fields because they aren't cck fields.
      if (in_array($field, self::AVOID_FIELDS)) {
        continue;
      }

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
