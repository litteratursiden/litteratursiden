<?php

namespace Drupal\lit_cover_service\Command;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\lit_cover_service\Service\BatchServiceInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush command file for Cover Service commands.
 */
class LitCoverServiceCommands extends DrushCommands {

  private const START_BATCH = '0';
  private const BATCH_SIZE = 100;

  private const ALL_BOOKS = 'all_books';
  private const HAS_COVER = 'has_cover';
  private const NO_COVER = 'no_cover';

  /**
   * Batch service.
   *
   * @var \Drupal\lit_cover_service\Service\BatchServiceInterface
   */
  private $batchService;

  /**
   * Entity type service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerChannelFactory;

  /**
   * Constructs a new UpdateVideosStatsController object.
   *
   * @param \Drupal\lit_cover_service\Service\BatchService $batchService
   *   Batch Service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger service.
   */
  public function __construct(BatchServiceInterface $batchService, EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->batchService = $batchService;
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactory = $loggerChannelFactory;
  }

  /**
   * Fix books with broken cover image references.
   *
   * @usage lit_cover_service:fix_references
   *
   * @command lit_cover_service:fix_references
   * @aliases fix_references
   */
  public function fixMissingReferences() {
    $startBatch = 0;

    $this->loggerChannelFactory->get('lit_cover_service')->info('Fix broken/missing cover references started');

    $nids = $this->getBrokenReferencesNids();
    $operations = [];

    if (!empty($nids)) {
      $chunks = array_chunk($nids, self::BATCH_SIZE);

      $numOperations = count($nids);
      $batchId = $startBatch;
      $batchTotal = count($chunks);

      $chunks = array_slice($chunks, $startBatch);
      foreach ($chunks as $batchNids) {
        $operations[] = [
          '\Drupal\lit_cover_service\Service\BatchService::missingCoverReferences',
          [
            $batchId,
            $batchTotal,
            $batchNids,
          ],
        ];
        ++$batchId;
      }
    }
    else {
      $this->logger()->warning('No nodes of this type @type', ['@type' => 'book']);
    }

    $batch = [
      'title' => t('Fixed broken cover references from @num "book" node(s)', ['@num' => $numOperations]),
      'operations' => $operations,
      'finished' => '\Drupal\lit_cover_service\Service\BatchService::batchFinished',
    ];
    batch_set($batch);
    drush_backend_batch_process();
    $this->loggerChannelFactory->get('lit_cover_service')->info('Broken cover references batch operations end.');
    $this->logger()->success("Fixed broken cover references");
  }

  /**
   * Batch delete cover images from 'Books'.
   *
   * This command is intended as a one time clean up command for migrating
   * from 'MoreInfo' to DDB Cover Service.
   *
   * @param string $startBatch
   *   The batch number to start with.
   *
   * @usage lit_cover_service:delete_covers 100
   *   Delete covers from 'Books', restart batch at 100
   *
   * @command lit_cover_service:delete_covers
   * @aliases delete_covers
   */
  public function deleteCovers(string $startBatch = self::START_BATCH): void {
    $startBatch = $this->startBatch($startBatch);

    $this->loggerChannelFactory->get('lit_cover_service')->info('Delete covers batch operations start');

    $nids = $this->getBookNids(self::HAS_COVER);

    if (!empty($nids)) {
      $chunks = array_chunk($nids, self::BATCH_SIZE);

      $numOperations = count($nids);
      $batchId = $startBatch;
      $batchTotal = count($chunks);

      $chunks = array_slice($chunks, $startBatch);
      foreach ($chunks as $batchNids) {
        $operations[] = [
          '\Drupal\lit_cover_service\Service\BatchService::deleteBookCovers',
          [
            $batchId,
            $batchTotal,
            $batchNids,
          ],
        ];
        ++$batchId;
      }
    }
    else {
      $this->logger()->warning('No nodes of this type @type', ['@type' => 'book']);
    }

    $batch = [
      'title' => t('Deleting covers from @num "book" node(s)', ['@num' => $numOperations]),
      'operations' => $operations,
      'finished' => '\Drupal\lit_cover_service\Service\BatchService::batchFinished',
    ];
    batch_set($batch);
    drush_backend_batch_process();
    $this->loggerChannelFactory->get('lit_cover_service')->info('Delete covers batch operations end.');
    $this->logger()->success("Book covers deleted");
  }

  /**
   * Batch fetch cover images for 'Books' for books with no cover.
   *
   * @param string $startBatch
   *   The batch number to start with.
   *
   * @usage lit_cover_service:fetch_covers 100
   *   Fetch covers for 'Books', restart batch at 100
   *
   * @command lit_cover_service:fetch_missing_covers
   * @aliases fetch_missing_covers
   */
  public function fetchMissingCovers(string $startBatch = self::START_BATCH): void {
    $startBatch = $this->startBatch($startBatch);

    $this->loggerChannelFactory->get('lit_cover_service')->info('Fetch covers batch operations start');

    // Load book nids for books without cover.
    $nids = $this->getBookNids(self::NO_COVER);

    if (!empty($nids)) {
      $chunks = array_chunk($nids, self::BATCH_SIZE);

      $numOperations = count($nids);
      $batchId = $startBatch;
      $batchTotal = count($chunks);

      $operations = [];
      $chunks = array_slice($chunks, $startBatch);
      foreach ($chunks as $batchNids) {
        $operations[] = [
          '\Drupal\lit_cover_service\Service\BatchService::fetchBookCovers',
          [
            $batchId,
            $batchTotal,
            $batchNids,
          ],
        ];
        ++$batchId;
      }

      $batch = [
        'title' => t('Fetching covers for @num "book" node(s)', ['@num' => $numOperations]),
        'operations' => $operations,
        'finished' => '\Drupal\lit_cover_service\Service\BatchService::batchFinished',
      ];
      batch_set($batch);
      drush_backend_batch_process();
      $this->loggerChannelFactory->get('lit_cover_service')->info('Fetch covers batch operations end.');
      $this->logger()->success("Book covers fetched");
    }
    else {
      $this->logger()->warning('No nodes of this type book');
    }
  }

  /**
   * Batch fetch cover images for all 'Books' replacing existing covers.
   *
   * @param string $startBatch
   *   The batch number to start with.
   *
   * @usage lit_cover_service:fetch_covers 100
   *   Fetch covers for 'Books', restart batch at 100
   *
   * @command lit_cover_service:add_or_replace_covers
   * @aliases add_or_replace_covers
   */
  public function addOrReplaceCovers(string $startBatch = self::START_BATCH): void {
    $startBatch = $this->startBatch($startBatch);

    $this->loggerChannelFactory->get('lit_cover_service')->info('Fetch covers batch operations start');

    // Load all book nids.
    $nids = $this->getBookNids();

    if (!empty($nids)) {
      $chunks = array_chunk($nids, self::BATCH_SIZE);

      $numOperations = count($nids);
      $batchId = $startBatch;
      $batchTotal = count($chunks);

      $operations = [];
      $chunks = array_slice($chunks, $startBatch);
      foreach ($chunks as $batchNids) {
        $operations[] = [
          '\Drupal\lit_cover_service\Service\BatchService::replaceBookCovers',
          [
            $batchId,
            $batchTotal,
            $batchNids,
          ],
        ];
        ++$batchId;
      }

      $batch = [
        'title' => t('Fetching and replaced covers for @num "book" node(s)', ['@num' => $numOperations]),
        'operations' => $operations,
        'finished' => '\Drupal\lit_cover_service\Service\BatchService::batchFinished',
      ];
      batch_set($batch);
      drush_backend_batch_process();
      $this->loggerChannelFactory->get('lit_cover_service')->info('Fetch or replace covers batch operations end.');
      $this->logger()->success("Book covers fetched");
    }
    else {
      $this->logger()->warning('No nodes of this type book');
    }
  }

  /**
   * Get node ids for all 'Book' nodes.
   *
   * @param string $cover
   *   Should the books have covers. Default 'all' books. Valid values are
   *   self::ALL_BOOKS, self::HAS_COVER, self::NO_COVER.
   *
   * @return array|null Array of nids.
   *   Array of nids.
   */
  private function getBookNids(string $cover = self::ALL_BOOKS): ?array {
    try {
      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'book')
        ->sort('nid', 'DESC');

      switch ($cover) {
        case self::ALL_BOOKS:
          break;

        case self::HAS_COVER:
          $query->exists('field_book_cover_image');
          break;

        case self::NO_COVER:
          $query->notExists('field_book_cover_image');
          break;

        default:
          throw new \InvalidArgumentException('Unknown value' . $cover);
      }

      return $query->execute();
    }
    catch (\Exception $e) {
      $this->logger->error('Error found @e', ['@e' => $e]);
    }

    return NULL;
  }

  /**
   * Find all node id's of books with broken references.
   *
   * @return array
   *   Array with node id's found.
   */
  private function getBrokenReferencesNids(): array {
    $database = \Drupal::database();
    $query = $database->select('node__field_book_cover_image', 'bci');
    $query->fields('bci', ['entity_id']);
    $query->leftJoin('file_managed', 'fm', 'fm.fid = bci.field_book_cover_image_target_id');
    $query->isNull('fm.uri');

    $result = $query->execute();

    $nids = $result->fetchAll();
    $nids = array_map(function ($item) {
      return $item->entity_id;
    }, $nids);

    return $nids;
  }

  /**
   * Parse string argument to integer value.
   *
   * @param string $startBatch
   *   The string value of the start batch.
   *
   * @return int
   *   The start batch as integer.
   */
  private function startBatch(string $startBatch): int {
    if ('0' === $startBatch) {
      return 0;
    }
    elseif (is_numeric($startBatch)) {
      $intBatch = intval($startBatch);
      // Returns the integer value of var on success, or 0 on failure.
      if (0 === $intBatch) {
        throw new \InvalidArgumentException($startBatch . ' must be an integer value');
      }
    }
    else {
      throw new \InvalidArgumentException($startBatch . ' must be an integer value');
    }

    return $intBatch;
  }

}
