<?php

/**
 * @file
 * Client to get access tokens from "adgangs platformen".
 *
 * Uses the existing Client defined in the "lit_open_platform" module. However
 * this client has a protected constructor so it can not be used directly.
 */

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
   *   Open Platform client id from from DBC
   * @param string $clientSecret
   *   Open Platform client secret from from DBC
   */
  public function __construct(string $clientId, string $clientSecret) {
    parent::__construct($clientId, $clientSecret);
  }
}
