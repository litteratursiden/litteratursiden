<?php

namespace Drupal\lit_xml;

use Drupal\node\Entity\Node;

/**
 * Interface TransformerInterface.
 */
interface TransformerInterface
{
  /**
   * @param Node $node
   * @return array
   */
  public static function transform(Node $node): array;

  /**
   * @param array $nodes
   * @return array
   */
  public static function transformCollection(array $nodes): array;
}
