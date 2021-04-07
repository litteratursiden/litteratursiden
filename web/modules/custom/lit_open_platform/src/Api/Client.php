<?php

namespace Drupal\lit_open_platform\Api;

use GuzzleHttp\Client as RequestClient;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Client.
 */
class Client {

  /**
   * The API version.
   */
  public const VERSION = 'v3';

  /**
   * The OAuth2 token url.
   */
  public const OAUTH2_TOKEM_URL = 'https://auth.dbc.dk/oauth/token';

  /**
   * The API base path.
   */
  public const API_BASE_PATH = 'https://openplatform.dbc.dk';

  /**
   * Cache buffer time in seconds for the access token.
   */
  private const CACHE_TOKEN_TIME_BUFFER = 10;

  /**
   * The client id.
   *
   * @var string
   */
  protected $clientId = '';

  /**
   * The client secret.
   *
   * @var string
   */
  protected $clientSecret = '';

  /**
   * The access token.
   *
   * @var array
   */
  protected $token = [];

  /**
   * The client instance.
   *
   * @var \Drupal\lit_open_platform\Api\Client
   */
  protected static $client;

  /**
   * Client constructor.
   *
   * @param string $clientId
   * @param string $clientSecret
   */
  protected function __construct(string $clientId, string $clientSecret) {
    $this->setClientId($clientId);
    $this->setClientSecret($clientSecret);
  }

  /**
   * Set the client id.
   *
   * @param string $clientId
   * @return $this
   */
  public function setClientId(string $clientId) {
    $this->clientId = $clientId;

    return $this;
  }

  /**
   * Get the client id.
   *
   * @return string
   */
  public function getClientId() {
    return $this->clientId;
  }

  /**
   * Set the client secret.
   *
   * @param string $clientSecret
   * @return $this
   */
  public function setClientSecret(string $clientSecret) {
    $this->clientSecret = $clientSecret;

    return $this;
  }

  /**
   * Get the client secret.
   *
   * @return string
   */
  public function getClientSecret() {
    return $this->clientSecret;
  }

  /**
   * Set the access token.
   *
   * @param array $token
   * @return $this
   */
  public function setAccessToken(array $token) {
    if ($this->verifyToken($token)) {
      $this->token = $token;

      // Cache access token.
      \Drupal::cache()->set('lit_open_platform_access_token', $this->token, time() + $this->token['expires_in'] - self::CACHE_TOKEN_TIME_BUFFER);
    }

    return $this;
  }

  /**
   * Get the access token.
   *
   * @return array
   */
  public function getAccessToken(): array {
    if ($cache = \Drupal::cache()->get('lit_open_platform_access_token')) {
      $this->token = $cache->data;
    }

    if (!$this->token) {
      $this->setAccessToken($this->requestAccessToken());
    }

    return $this->token;
  }

  /**
   * Check if the access token is valid.
   *
   * @param array $token
   * @return bool
   */
  public function verifyToken(array $token): bool {
    return isset($token['token_type'], $token['access_token'], $token['expires_in']);
  }

  /**
   * Get token for basic auth.
   *
   * @return string
   */
  public function getBasicToken(): string {
    return base64_encode($this->getClientId() . ':' . $this->getClientSecret());
  }

  /**
   * Build request url to the API.
   *
   * @param string $uri
   * @return string
   */
  protected function buildUrl(string $uri): string {
    return implode('/', [self::API_BASE_PATH, self::VERSION, ltrim($uri, '/')]);
  }

  /**
   * Request for the access token.
   *
   * @return array
   */
  public function requestAccessToken(): array {
    return $this->request('POST', self::OAUTH2_TOKEM_URL, [
      'headers' => [
        'Authorization' => 'Basic ' . $this->getBasicToken(),
        'Content-type' => 'application/x-www-form-urlencoded',
      ],
      'form_params' => [
        'grant_type' => 'password',
        'username' => '@',
        'password' => '@',
      ],
    ]);
  }

  /**
   * Send a request.
   *
   * @param string $method
   * @param string $uri
   * @param array $options
   *
   * @return array|mixed
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function request(string $method, string $uri, array $options = []) {
    $client = new RequestClient();

    $result = [];

    try {
      $response = $client->request($method, $uri, $options);

      $result = json_decode($response->getBody(), TRUE);
    }
    catch (ClientException $exception) {
      drupal_set_message("The Open Platform " . $exception->getMessage(), 'error');
    }

    return $result;
  }

  /**
   * Get instance of client.
   *
   * @param string $clientId
   * @param string $clientSecret
   * @return \Drupal\lit_open_platform\Api\Client
   */
  public static function getInstance(string $clientId, string $clientSecret) {
    return static::$client = static::$client ?? new static($clientId, $clientSecret);
  }

}
