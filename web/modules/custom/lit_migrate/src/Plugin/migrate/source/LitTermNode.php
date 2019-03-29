<?php

namespace Drupal\lit_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\taxonomy\Plugin\migrate\source\d6\TermNode;

/**
 * @MigrateSource(
 *   id = "lit_term_node",
 *   source_provider = "taxonomy"
 * )
 */
class LitTermNode extends TermNode {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('term_node', 'tn')
      ->distinct()
      ->fields('tn', ['nid', 'vid'])
      ->fields('n', ['type']);
    // Because this is an inner join it enforces the current revision.
    $query->innerJoin('term_data', 'td', 'td.tid = tn.tid AND td.vid = :vid', [':vid' => $this->configuration['vid']]);
    $query->innerJoin('node', 'n', static::JOIN);
    $query->condition('n.type', $this->configuration['node_type']);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Select the terms belonging to the revision selected.
    $query = $this->select('term_node', 'tn')
      ->fields('tn', ['tid'])
      ->condition('n.nid', $row->getSourceProperty('nid'));
    $query->join('node', 'n', static::JOIN);
    $query->innerJoin('term_data', 'td', 'td.tid = tn.tid AND td.vid = :vid', [':vid' => $this->configuration['vid']]);
    $query->condition('n.type', $this->configuration['node_type']);
    $row->setSourceProperty('tid', $query->execute()->fetchCol());
    return parent::prepareRow($row);
  }
}
