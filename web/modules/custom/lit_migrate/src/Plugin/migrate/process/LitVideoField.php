<?php

namespace Drupal\lit_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "lit_video_field"
 * )
 */
class LitVideoField extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    switch ($value['provider']) {
      case 'merry':
        // Build correct url to merry video.
        $value['value'] = 'http://merry.aakb.dk/litteratursiden/' . $value['value'] . '.m4v';
        break;

      case 'youtube':
        // Build correct url to youtube video.
        $value['value'] = 'https://youtu.be/' . $value['value'];
        break;

      case 'vimeo':
        // Build correct url to vimeo video.
        $value['value'] = 'http://vimeo.com/' . $value['value'];
        break;

      case '23video':
        // Build correct link to 23video video.
        $value['value'] = $value['embed'];
        break;

      default:
        break;
    }

    return $value;
  }

}
