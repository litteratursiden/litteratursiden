<?php

namespace Drupal\lit_open_platform\Api;

/**
 * Interface SearchClientInterface
 */
interface SearchClientInterface {

  /**
   * Search for a library material.
   *
   * @param string $search
   * @param array $fields
   * @return array
   */
  public function requestSearch(string $search, array $fields = []): array;

  /**
   * Retrieve meta information about a creative work.
   *
   * @param array $pids
   * @param array $fields
   * @return array
   */
  public function requestWork(array $pids, array $fields = []): array;

}
