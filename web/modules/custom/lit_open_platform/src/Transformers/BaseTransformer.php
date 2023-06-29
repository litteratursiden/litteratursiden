<?php

namespace Drupal\lit_open_platform\Transformers;

/**
 * Class BaseTranformer.
 */
abstract class BaseTransformer implements TransformerInterface {

  /**
   * Transform a collection.
   *
   * @param array $items
   *   A list of items to transform.
   *
   * @return array
   *   A list of mapped items.
   */
  public static function transformCollection(array $items): array {
    return array_map('static::transform', $items);
  }

}
