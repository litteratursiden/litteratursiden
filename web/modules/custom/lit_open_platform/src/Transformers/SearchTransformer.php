<?php

namespace Drupal\lit_open_platform\Transformers;

/**
 * Class SearchTransformer.
 */
class SearchTransformer extends BaseTransformer {

  /**
   * {@inheritdoc}
   */
  public static function transform($item): array {
    return [
      'pid' => (string) $item['pid'][0],
      'title' => $item['dcTitleFull'][0] ?? NULL,
      'image' => $item['coverUrl42'][0] ?? NULL,
      'author' => $item['creatorAut'][0] ?? NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function transformCollection(array $items): array {
    $result = [];
    foreach ($items as $item) {
      $item = static::transform($item);

      // Apply the pid => title format.
      $result[$item['pid']] = $item;
    }

    return $result;
  }

}
