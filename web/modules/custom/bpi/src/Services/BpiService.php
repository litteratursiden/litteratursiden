<?php

namespace Drupal\bpi\Services;

use Bpi\Sdk\Bpi;

/**
 * Class BpiService.
 *
 * Provides access to BPI logic.
 */
class BpiService {

  /**
   * The BPI Instance.
   *
   * @var \Bpi\Sdk\Bpi|null
   */
  private ?Bpi $bpiInstance = NULL;

  /**
   * BpiService constructor.
   *
   * @param string $url
   *   Bpi service endpoint.
   * @param string $agency
   *   Agency id.
   * @param string $publicKey
   *   Public key.
   * @param string $privateKey
   *   Private key.
   */
  public function __construct(string $url, string $agency, string $publicKey, string $privateKey) {
    $this->bpiInstance = new Bpi($url, $agency, $publicKey, $privateKey);
  }

  /**
   * Allows to check connectivity to the BPI service.
   *
   * This method is primarily used in settings form, validation
   * method, when config settings are not yet available.
   *
   * @param string $url
   *   Bpi service endpoint.
   * @param string $agency
   *   Agency id.
   * @param string $publicKey
   *   Public key.
   * @param string $privateKey
   *   Private key.
   */
  public function checkConnectivity(string $url, string $agency, string $publicKey, string $privateKey): void {
    $bpi = new Bpi($url, $agency, $publicKey, $privateKey);

    // Fake a request, to check connectivity.
    $bpi->getDictionaries();
  }

  /**
   * Returns current bpi instance.
   *
   * @return \Bpi\Sdk\Bpi|null
   *   BPI instance.
   */
  public function getInstance(): ?Bpi {
    return $this->bpiInstance;
  }

}
