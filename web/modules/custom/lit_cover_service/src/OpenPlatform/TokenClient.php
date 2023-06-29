<?php

namespace Drupal\lit_cover_service\OpenPlatform;

use Drupal\lit_open_platform\Api\Client;

/**
 * Class TokenClient.
 */
class TokenClient extends Client {

  /**
   * TokenClient constructor.
   *
   * @param string $clientId
   *   Open Platform client id from from DBC.
   * @param string $clientSecret
   *   Open Platform client secret from from DBC.
   */
  public function __construct(string $clientId, string $clientSecret) {
    parent::__construct($clientId, $clientSecret);
  }

}
