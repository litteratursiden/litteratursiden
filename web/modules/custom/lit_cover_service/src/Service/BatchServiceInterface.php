<?php

namespace Drupal\lit_cover_service\Service;


/**
 * Class BatchService.
 */
interface BatchServiceInterface {

  /**
   * Batch delete process callback.
   *
   * @param int $batchId
   *   Id of the batch
   * @param $batchTotal
   *   Total number of batch operations
   * @param array $nids
   *   The nids to process
   * @param object $context
   *   Context for operations
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteBookCovers($batchId, $batchTotal, $nids, &$context);

  /**
   * Batch fetch process callback.
   *
   * @param int $batchId
   *   Id of the batch
   * @param $batchTotal
   *   Total number of batch operations
   * @param array $nids
   *   The nids to process
   * @param object $context
   *   Context for operations
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function fetchBookCovers($batchId, $batchTotal, $nids, &$context);

  /**
   * Replace process callback.
   *
   * @param int $batchId
   *   Id of the batch
   * @param $batchTotal
   *   Total number of batch operations
   * @param array $nids
   *   The nids to process
   * @param object $context
   *   Context for operations
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function replaceBookCovers($batchId, $batchTotal, $nids, &$context);

  /**
   * Batch Finished callback.
   *
   * @param bool $success
   *   Success of the operation.
   * @param array $results
   *   Array of results for post processing.
   * @param array $operations
   *   Array of operations.
   */
  public function batchFinished($success, array $results, array $operations);

}
