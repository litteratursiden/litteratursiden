<?php

/**
 * @file
 * Batch service to support the commands defined in the module.
 */

namespace Drupal\lit_cover_service\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

/**
 * Class BatchService.
 */
class BatchService implements BatchServiceInterface {

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
    $deletedImageFiles = [];
    foreach ($nodes as $node) {
      $deletedImageFiles[] = self::clearImageField($node);
      $node->save();

      // Store some result for post-processing in the finished callback.
      $context['results'][] = $node->nid;

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $node->nid;
    }

    self::deleteFileEntities($deletedImageFiles);

    // Inform the batch engine that we are not finished,
    // and provide an estimation of the completion level we reached.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

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
  public function fetchBookCovers($batchId, $batchTotal, $nids, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = $batchTotal;
    }

    $context['message'] = t('Fetching batch @batchId of @batchTotal', [
      '@batchId' => $batchId,
      '@batchTotal' => $batchTotal,
    ]);

    $nodes = Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      self::setImageFile($node);
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
  public function replaceBookCovers($batchId, $batchTotal, $nids, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = $batchTotal;
    }

    $context['message'] = t('Fetching batch @batchId of @batchTotal', [
      '@batchId' => $batchId,
      '@batchTotal' => $batchTotal,
    ]);

    $nodes = Node::loadMultiple($nids);
    $deletedImageFiles = [];
    foreach ($nodes as $node) {
      $deletedImageFiles[] = self::clearImageField($node);
      self::setImageFile($node);

      $node->save();

      // Store some result for post-processing in the finished callback.
      $context['results'][] = $node->nid;

      // Update our progress information.
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $node->nid;
    }

    self::deleteFileEntities($deletedImageFiles);

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
  public function batchFinished($success, array $results, array $operations) {
    $messenger = \Drupal::messenger();
    if ($success) {
      $messenger->addMessage(t('@count books processed.', ['@count' => count($results)]));
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

  /**
   * Clear the cover image field
   *
   * @param \Drupal\node\Entity\Node $node
   *
   * @return \Drupal\file\Entity\File|null
   */
  private static function clearImageField(Node $node): ?File {
    /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $deletedImageField */
    $deletedImageField = $node->get('field_book_cover_image');
    $file = $deletedImageField->entity;
    $node->set('field_book_cover_image', NULL);

    return $file;
  }

  /**
   * Set the image file
   *
   * @param \Drupal\node\Entity\Node $node
   */
  private static function setImageFile(Node $node): void {
    $isbn = $node->get('field_book_isbn')->getString();
    if ($isbn) {
      $coverService = \Drupal::service('lit_cover_service.cover_service');
      $coverImage = $coverService->getCoverImage($isbn);

      if ($coverImage) {
        $imageField = [
          'target_id' => $coverImage->id(),
          'alt' => '',
          'title' => $node->getTitle(),
        ];
        $node->set('field_book_cover_image', $imageField);
      }
    }
  }

  /**
   * Delete files
   *
   * @param \Drupal\file\Entity\File[] $files
   */
  private static function deleteFileEntities(array $files): void {
    $files = array_filter($files);

    try {
      /** @var EntityTypeManagerInterface $entityManager */
      $entityManager = \Drupal::service('entity.manager');
      $storage = $entityManager->getStorage('file');
      $storage->delete($files);
    } catch (\Exception $e) {
      \Drupal::logger('lit_cover_service')->error($e->getMessage());
    }
  }
}
