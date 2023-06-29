<?php

namespace Drupal\lit_open_platform\Transformers;

/**
 * Interface for transformer.
 */
interface TransformerInterface {

  /**
   * Transform an item.
   *
   * @param array $item
   *   The item to transform.
   *
   * @return array
   *   A list.
   */
  public static function transform(array $item): array;

  /**
   * Transform a collection.
   *
   * @param array $items
   *   A list of items to transform.
   *
   * @return array
   *   The transformed items.
   */
  public static function transformCollection(array $items): array;

}
