<?php

/**
 * @file
 * Commands for cover clean up. Deletes and download covers from DDB Cover Service.
 */

namespace Drupal\lit_cover_service\Command;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\lit_cover_service\Service\BatchService;
use Drush\Commands\DrushCommands;

/**
 * Drush command file for Cover Service commands.
 */
class LitCoverServiceCommands extends DrushCommands {

  private const LIMIT_ALL = 'all';
  private const BATCH_SIZE = 100;

  private const ALL_BOOKS = 'all_books';
  private const HAS_COVER = 'has_cover';
  private const NO_COVER = 'no_cover';

  /**
   * Batch service
   *
   * @var \Drupal\lit_cover_service\Service\BatchService
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
  public function __construct(BatchService $batchService, EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->batchService = $batchService;
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactory = $loggerChannelFactory;
  }

  /**
   * Batch delete cover images from 'Books'
   *
   * This command is intended as a one time clean up command for migrating
   * from 'MoreInfo' to DDB Cover Service.
   *
   * @param $limit
   *   Limit the number of covers to delete
   *
   * @usage lit_cover_service:delete_covers 100
   *   Delete covers from 'Books', limit to 100
   *
   * @command lit_cover_service:delete_covers
   * @aliases delete_covers
   */
  public function deleteCovers($limit = self::LIMIT_ALL) {
    $limit = $this->parseLimit($limit);

    $this->loggerChannelFactory->get('lit_cover_service')->info('Delete covers batch operations start');

    $nids = $this->getBookNids($limit, self::HAS_COVER);

    if (!empty($nids)) {
      $chunks = array_chunk($nids, self::BATCH_SIZE);

      $numOperations = count($nids);
      $batchId = 1;
      $batchTotal = count($chunks);

      foreach ($chunks as $batchNids) {
        $operations[] = [
          '\Drupal\lit_cover_service\Service\BatchService::deleteBookCovers',
          [
            $batchId,
            $batchTotal,
            $batchNids
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
   * Batch fetch cover images for 'Books' for books with no cover
   *
   * @param $limit
   *   Limit the number of covers to fetch
   *
   * @usage lit_cover_service:fetch_covers 100
   *   Fetch covers for 'Books', limit to 100
   *
   * @command lit_cover_service:fetch_missing_covers
   * @aliases fetch_missing_covers
   */
  public function fetchMissingCovers($limit = self::LIMIT_ALL) {
    $limit = $this->parseLimit($limit);

    $this->loggerChannelFactory->get('lit_cover_service')->info('Fetch covers batch operations start');

    // Load book nids for books without cover.
    $nids = $this->getBookNids($limit, self::NO_COVER);

    if (!empty($nids)) {
      $chunks = array_chunk($nids, self::BATCH_SIZE);

      $numOperations = count($nids);
      $batchId = 1;
      $batchTotal = count($chunks);

      $operations = [];
      foreach ($chunks as $batchNids) {
        $operations[] = [
          '\Drupal\lit_cover_service\Service\BatchService::fetchBookCovers',
          [
            $batchId,
            $batchTotal,
            $batchNids
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
   * @param $limit
   *   Limit the number of covers to fetch
   *
   * @usage lit_cover_service:fetch_covers 100
   *   Fetch covers for 'Books', limit to 100
   *
   * @command lit_cover_service:add_or_replace_covers
   * @aliases add_or_replace_covers
   */
  public function addOrReplaceCovers($limit = self::LIMIT_ALL) {
    $limit = $this->parseLimit($limit);

    $this->loggerChannelFactory->get('lit_cover_service')->info('Fetch covers batch operations start');

    // Load all book nids
    $nids = $this->getBookNids($limit);

    if (!empty($nids)) {
      $chunks = array_chunk($nids, self::BATCH_SIZE);

      $numOperations = count($nids);
      $batchId = 1;
      $batchTotal = count($chunks);

      $operations = [];
      foreach ($chunks as $batchNids) {
        $operations[] = [
          '\Drupal\lit_cover_service\Service\BatchService::replaceBookCovers',
          [
            $batchId,
            $batchTotal,
            $batchNids
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
   * @param int $limit
   *   The max number of nids to get
   *
   * @param string $cover
   *   Should the books have covers. Default 'all' books. Valid values are
   *   self::ALL_BOOKS, self::HAS_COVER, self::NO_COVER
   *
   * @return array
   *   Array of nids
   */
  private function getBookNids(int $limit, string $cover = self::ALL_BOOKS): array
  {
    try {
      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery()
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

      if ($limit) {
        $query->range(0, $limit);
      }
      return $query->execute();
    }
    catch (\Exception $e) {
      $this->logger->error('Error found @e', ['@e' => $e]);
    }
  }

  /**
   * Parse string argument to integer value.
   *
   * @param string $limit
   *   The string value of the limit. Either self::LIMIT_ALL or integer value.
   *
   * @return int
   *   The limit as integer. "0" returned for no limit
   */
  private function parseLimit(string $limit): int
  {
    if (self::LIMIT_ALL === $limit) {
      $intLimit = 0;
    }
    else if (is_numeric($limit)) {
      $intLimit = intval($limit);
      // Returns the integer value of var on success, or 0 on failure.
      if (0 === $intLimit) {
        throw new \InvalidArgumentException($limit . ' must be an integer value');
      }
    }
    else {
      throw new \InvalidArgumentException($limit . ' must be an integer value');
    }

    return $intLimit;
  }
}
