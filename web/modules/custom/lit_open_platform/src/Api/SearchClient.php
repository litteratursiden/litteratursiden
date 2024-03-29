<?php

namespace Drupal\lit_open_platform\Api;

/**
 * Class for SearchClient.
 *
 * @todo check different client creating, static $client
 */
class SearchClient extends Client implements SearchClientInterface {

  /**
   * Types to search for in open search.
   */
  private const MATERIAL_TYPES = [
    'bog',
    'billedbog',
    'graphic novel',
    'tegneserie',
  ];

  /**
   * {@inheritdoc}
   */
  public function requestSearch(string $search, array $fields = [
    'pid',
    'dcTitleFull',
    'coverUrl42',
    'creatorAut',
  ]): array {
    $types = implode(' OR ', array_map(function ($type) {
      return 'term.type="' . $type . '"';
    }, self::MATERIAL_TYPES));

    $response = $this->request('POST', $this->buildUrl('search'), [
      'json' => [
        'pretty' => TRUE,
        'access_token' => $this->getAccessToken()['access_token'],
        'q' => str_replace(' ', ' AND ', trim($search)) . " AND term.language=Dansk AND (" . $types . ") AND (term.accessType=physical OR term.accessType=online) NOT term.workType=movie NOT term.workType=article",
        'fields' => $fields,
      ],
    ]);

    return $response['data'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function requestWork(array $pids, array $fields = [
    'pid', 'dcTitleFull', 'identifierISBN', 'coverUrlFull', 'creatorAut', 'abstract',
    'source', 'contributorTrl', 'date', 'publisher', 'extent', 'language', 'subjectDK5',
    'subjectDK5Text', 'subjectDBCS', 'subjectDBCN', 'subjectDBCF',
  ]): array {
    $response = $this->request('POST', $this->buildUrl('work'), [
      'json' => [
        'pretty' => TRUE,
        'access_token' => $this->getAccessToken()['access_token'],
        'pids' => $pids,
        'fields' => $fields,
      ],
    ]);

    return isset($response['data']) ? array_filter($response['data']) : [];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function requestPidByIsbn(string $isbn): ?string {
    $response = $this->request('POST', $this->buildUrl('search'), [
      'json' => [
        'pretty' => TRUE,
        'access_token' => $this->getAccessToken()['access_token'],
        'q' => "term.isbn=$isbn AND term.type=Bog",
        'fields' => ['pid'],
      ],
    ]);

    return $response['data'][0]['pid'][0] ?? NULL;
  }

  /**
   * Get instance of client.
   *
   * @param string $clientId
   *   The client id.
   * @param string $clientSecret
   *   The client secret.
   *
   * @return \Drupal\lit_open_platform\Api\SearchClientInterface
   *   The lit open platform api client.
   */
  public static function getInstance(string $clientId, string $clientSecret): SearchClient {
    return static::$client = static::$client ?? new static($clientId, $clientSecret);
  }

}
