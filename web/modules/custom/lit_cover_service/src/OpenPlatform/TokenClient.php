<?php

namespace Drupal\lit_cover_service\OpenPlatform;

use Drupal\lit_open_platform\Api\Client;

/**
 * Class TokenClient
 */
class TokenClient extends Client {

  /**
   * TokenClient constructor.
   *
   * @param string $clientId
   * @param string $clientSecret
   */
  public function __construct(string $clientId, string $clientSecret) {
    parent::__construct($clientId, $clientSecret);
  }
}
