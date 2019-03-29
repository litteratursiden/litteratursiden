<?php

namespace Drupal\lit_xml;

/**
 * Class BaseTransformer
 * @package Drupal\lit_xml\Transformers
 */
abstract class BaseTransformer implements TransformerInterface
{
  /**
   * @param array $nodes
   * @return array
   */
  public static function transformCollection(array $nodes): array
  {
      return array_map('static::transform', $nodes);
  }

  /**
   * @param string|null $string
   * @param int $length
   * @return string
   */
  protected static function teaser(?string $string, int $length = 100): string {
    $string = strip_tags($string);

    $pos = strlen($string) > $length ? strpos($string, '.', $length) : FALSE;

    return $pos ? substr($string, 0, $pos+1) : $string;
  }

}
