<?php

namespace Drupal\lit_open_platform\Transformers;

/**
 * Class BaseTranformer.
 */
abstract class BaseTransformer implements TransformerInterface
{
    /**
     * @param array $items
     * @return array
     */
    public static function transformCollection(array $items): array
    {
        return array_map('static::transform', $items);
    }
}
