<?php

namespace Drupal\lit_open_platform\Api;

/**
 * Interface for SearchClient.
 */
interface SearchClientInterface {

  /**
   * Search for a library material.
   *
   * @param string $search
   *   A search string.
   * @param array $fields
   *   A list of fields.
   *
   * @return array
   *   A client request.
   */
  public function requestSearch(string $search, array $fields = []): array;

  /**
   * Retrieve meta information about a creative work.
   *
   * @param array $pids
   *   A list of pids.
   * @param array $fields
   *   A list of fields.
   *
   * @return array
   *   A filtered client request.
   */
  public function requestWork(array $pids, array $fields = []): array;

}
