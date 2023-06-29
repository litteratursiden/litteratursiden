<?php

namespace Drupal\lit_open_platform\Transformers;

/**
 * Interface TransformerInterface.
 */
interface TransformerInterface {

  /**
   * @param $item
   * @return array
   */
  public static function transform($item): array;

  /**
   * @param array $items
   * @return array
   */
  public static function transformCollection(array $items): array;

}
