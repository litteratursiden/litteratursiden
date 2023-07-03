<?php

namespace Drupal\lit_open_platform\Api;

use GuzzleHttp\Client as RequestClient;

/**
 * A class for GuzzleHttp Client.
 */
class Client {

  /**
   * The API version.
   */
  public const VERSION = 'v3';

  /**
   * The OAuth2 token url.
   */
  public const OAUTH2_TOKEN_URL = 'https://auth.dbc.dk/oauth/token';

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
  protected string $clientId = '';

  /**
   * The client secret.
   *
   * @var string
   */
  protected string $clientSecret = '';

  /**
   * The access token.
   *
   * @var array
   */
  protected array $token = [];

  /**
   * The client instance.
   *
   * @var \Drupal\lit_open_platform\Api\Client
   */
  protected static Client $client;

  /**
   * Client constructor.
   *
   * @param string $clientId
   *   The client id.
   * @param string $clientSecret
   *   The client secret.
   */
  final public function __construct(string $clientId, string $clientSecret) {
    $this->setClientId($clientId);
    $this->setClientSecret($clientSecret);
  }

  /**
   * Set the client id.
   *
   * @param string $clientId
   *   The clinet id.
   *
   * @return $this
   *   The class with client id.
   */
  public function setClientId(string $clientId): self {
    $this->clientId = $clientId;

    return $this;
  }

  /**
   * Get the client id.
   *
   * @return string
   *   The client id.
   */
  public function getClientId(): string {
    return $this->clientId;
  }

  /**
   * Set the client secret.
   *
   * @param string $clientSecret
   *   A client secret.
   *
   * @return $this
   *   The class with client secret.
   */
  public function setClientSecret(string $clientSecret): self {
    $this->clientSecret = $clientSecret;

    return $this;
  }

  /**
   * Get the client secret.
   *
   * @return string
   *   The client secret.
   */
  public function getClientSecret(): string {
    return $this->clientSecret;
  }

  /**
   * Set the access token.
   *
   * @param array $token
   *   The access token.
   *
   * @return $this
   *   The class with access token included.
   */
  public function setAccessToken(array $token): self {
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
   *   The access token.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
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
   *   The access token.
   *
   * @return bool
   *   Whether the token validates.
   */
  public function verifyToken(array $token): bool {
    return isset($token['token_type'], $token['access_token'], $token['expires_in']);
  }

  /**
   * Get token for basic auth.
   *
   * @return string
   *   A base64 encoded client id and secret.
   */
  public function getBasicToken(): string {
    return base64_encode($this->getClientId() . ':' . $this->getClientSecret());
  }

  /**
   * Build request url to the API.
   *
   * @param string $uri
   *   The api uri.
   *
   * @return string
   *   A full api url.
   */
  protected function buildUrl(string $uri): string {
    return implode('/', [self::API_BASE_PATH, self::VERSION, ltrim($uri, '/')]);
  }

  /**
   * Request for the access token.
   *
   * @return array
   *   Te access token.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function requestAccessToken(): array {
    return $this->request('POST', self::OAUTH2_TOKEN_URL, [
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
   *   The request method.
   * @param string $uri
   *   The request uri.
   * @param array $options
   *   THe request options.
   *
   * @return array|mixed
   *   The request result.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function request(string $method, string $uri, array $options = []): mixed {
    $client = new RequestClient();

    $result = [];

    try {
      $response = $client->request($method, $uri, $options);

      $result = json_decode($response->getBody(), TRUE, 512, JSON_THROW_ON_ERROR);
    }
    catch (\Exception $exception) {
      \Drupal::messenger()
        ->addMessage("The Open Platform " . $exception->getMessage(), 'error');
    }

    return $result;
  }

  /**
   * Get instance of client.
   *
   * @param string $clientId
   *   The client id.
   * @param string $clientSecret
   *   The client secret.
   *
   * @return \Drupal\lit_open_platform\Api\Client
   *   The lit open platform api client.
   */
  public static function getInstance(string $clientId, string $clientSecret): Client {
    return static::$client = static::$client ?? new static($clientId, $clientSecret);
  }

}
