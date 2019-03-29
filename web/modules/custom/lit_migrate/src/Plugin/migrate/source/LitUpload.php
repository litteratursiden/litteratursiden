<?php

namespace Drupal\lit_migrate\Plugin\migrate\source;

use Drupal\file\Plugin\migrate\source\d6\Upload;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "lit_upload",
 *   source_provider = "upload"
 * )
 */
class LitUpload extends Upload {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('upload', 'u')
      ->distinct()
      ->fields('u', ['nid', 'vid']);
    $query->innerJoin('node', 'n', static::JOIN);
    $query->addField('n', 'type');
    $query->condition('n.type', $this->configuration['node_type']);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $query = $this->select('upload', 'u')
      ->fields('u', ['fid', 'description', 'list'])
      ->condition('u.nid', $row->getSourceProperty('nid'))
      ->orderBy('u.weight');
    $query->innerJoin('node', 'n', static::JOIN);
    $query->condition('n.type', $this->configuration['node_type']);
    $row->setSourceProperty('upload', $query->execute()->fetchAll());
    return parent::prepareRow($row);
  }

}
