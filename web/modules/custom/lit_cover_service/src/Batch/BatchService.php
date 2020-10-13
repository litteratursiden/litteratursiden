<?php

namespace Drupal\lit_cover_service\Batch;

use Drupal\node\Entity\Node;

/**
 * Class BatchService.
 */
class BatchService {

  /**
   * Batch process callback.
   *
   * @param int $batchId
   *   Id of the batch
   * @param $batchTotal
   *   Total number of batch operations
   * @param array $nids
   *   The nids to process
   * @param object $context
   *   Context for operations
   */
  public function deleteBookCovers($batchId, $batchTotal, $nids, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = $batchTotal;
    }

    $context['message'] = t('Deleting batch @batchId of @batchTotal', [
      '@batchId' => $batchId,
      '@batchTotal' => $batchTotal,
    ]);

    $nodes = Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      $node->set('field_book_cover_image', NULL);
      $node->save();

      // Store some result for post-processing in the finished callback.
      $context['results'][] = $node->nid;

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $node->nid;
    }

    // Inform the batch engine that we are not finished,
    // and provide an estimation of the completion level we reached.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

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
  public function deleteBookCoversFinished($success, array $results, array $operations) {
    $messenger = \Drupal::messenger();
    if ($success) {
      $messenger->addMessage(t('@count covers deleted.', ['@count' => count($results)]));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $messenger->addMessage(
        t('An error occurred while processing @operation with arguments : @args',
          [
            '@operation' => $error_operation[0],
            '@args' => print_r($error_operation[0], TRUE),
          ]
        )
      );
    }
  }
}
