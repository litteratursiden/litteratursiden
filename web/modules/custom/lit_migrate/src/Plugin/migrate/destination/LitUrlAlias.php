<?php

namespace Drupal\lit_migrate\Plugin\migrate\destination;

use Drupal\migrate\Row;
use Drupal\path\Plugin\migrate\destination\UrlAlias;

/**
 * @MigrateDestination(
 *   id = "lit_url_alias"
 * )
 */
class LitUrlAlias extends UrlAlias {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $nid = $row->getDestinationProperty('source');

    $row->setDestinationProperty('source', '/node/' . $nid);

    return parent::import($row, $old_destination_id_values);
  }

  /**
   * {@inheritdoc}
   */
  public function rollback(array $destination_identifier) {
    $this->aliasStorage->delete(['pid' => $destination_identifier]);
  }

}
