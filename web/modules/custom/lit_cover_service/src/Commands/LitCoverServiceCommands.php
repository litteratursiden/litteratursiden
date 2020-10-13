<?php

namespace Drupal\lit_cover_service\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush command file.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class LitCoverServiceCommands extends DrushCommands {

  private const LIMIT_ALL = 'all';
  private const BATCH_SIZE = 100;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactory = $loggerChannelFactory;
  }

  /**
   * Batch delete cover images from 'Books'
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

    $nids = $this->getBookWithCoversNids($limit);

    if (!empty($nids)) {
      $chunks = array_chunk($nids, self::BATCH_SIZE);

      $numOperations = count($nids);
      $batchId = 1;
      $batchTotal = count($chunks);

      foreach ($chunks as $batchNids) {
        $operations[] = [
          '\Drupal\lit_cover_service\Batch\BatchService::deleteBookCovers',
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
      'finished' => '\Drupal\lit_cover_service\Batch\BatchService::deleteBookCoversFinished',
    ];
    batch_set($batch);
    drush_backend_batch_process();
    $this->loggerChannelFactory->get('lit_cover_service')->info('Delete covers batch operations end.');
    $this->logger()->success("Book covers deleted");
  }

  /**
   * Get node ids for all 'Book' nodes with cover images.
   *
   * @param int $limit
   *
   * @return array
   */
  private function getBookWithCoversNids(int $limit): array
  {
    try {
      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery()
        ->condition('type', 'book')
        ->exists('field_book_cover_image')
        ->sort('nid', 'DESC');
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
   *
   * @return int
   */
  private function parseLimit(string $limit): int
  {
    if (self::LIMIT_ALL === $limit) {
      $intLimit = 0;
    } else if (is_numeric($limit)) {
      $intLimit = intval($limit);
      // Returns the integer value of var on success, or 0 on failure.
      if (0 === $intLimit) {
        throw new \InvalidArgumentException($limit . ' must be an integer value');
      }
    } else {
      throw new \InvalidArgumentException($limit . ' must be an integer value');
    }

    return $intLimit;
  }
}
