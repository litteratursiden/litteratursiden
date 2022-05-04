<?php

/**
 * @file
 * Cover service to find local covers or fetch remote covers bu ISBN.
 */

namespace Drupal\lit_cover_service\Service;

use CoverService\Api\CoverApi;
use CoverService\Configuration;
use Drupal\Core\File\Exception\DirectoryNotReadyException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\lit_cover_service\OpenPlatform\TokenClient;
use GuzzleHttp\ClientInterface;

/**
 * Class CoverService
 */
class CoverService implements CoverServiceInterface {

  private const COVER_SERVICE_HOST = 'https://cover.dandigbib.org';
  private const DRUPAL_FILE_PATH = 'public://cover_dandigbib_org';

  private $httpClient;
  private $fileSystem;

  /**
   * CoverService constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   */
  public function __construct(ClientInterface $httpClient, FileSystemInterface $fileSystem) {
    $this->httpClient = $httpClient;
    $this->fileSystem = $fileSystem;
  }

  /**
   * Get cover image from ISBN
   *
   * @param string $isbn
   *
   * @return \Drupal\file\FileInterface|null
   */
  public function getCoverImage(string $isbn): ?FileInterface {
    $isbn = trim($isbn);
    $isbn = str_replace('-', '', $isbn);

    $file = $this->findLocalImageFile($isbn);
    if ($file) {
      return $file;
    }

    $url = $this->getCoverUrlForIsbn($isbn);
    if ($url) {
      return $this->fetchRemoteImageFile($url);
    }

    return NULL;
  }

  /**
   * Find local cover file from ISBN
   *
   * @param string $isbn
   *
   * @return \Drupal\file\FileInterface|null
   */
  private function findLocalImageFile(string $isbn): ?FileInterface {
    $result = \Drupal::entityQuery('file')
      ->condition('uri', self::DRUPAL_FILE_PATH.'/'.$isbn.'.', 'STARTS_WITH')
      ->execute();

    if ($result) {
      $fid = array_shift($result);
      return File::load($fid);
    }

    return NULL;
  }

  /**
   * @param string $isbn
   *
   * @return string|null
   */
  private function getCoverUrlForIsbn(string $isbn): ?string {
    $originalImageUrl = NULL;
    $largeImageUrl = NULL;

    try {
      $accessToken = $this->getToken();
      $config = Configuration::getDefaultConfiguration()
        ->setAccessToken($accessToken)
        ->setHost(self::COVER_SERVICE_HOST);

      $apiInstance = new CoverApi(
        $this->httpClient,
        $config
      );

      $coverCollection = $apiInstance->getCoverCollection('isbn', [$isbn], [
        'original',
        'large',
      ]);

      foreach ($coverCollection as $cover) {
        $imageUrls = $cover->getImageUrls();
        foreach ($imageUrls as $imageUrl) {
          switch ($imageUrl->getSize()) {
            case 'original':
              $originalImageUrl = $imageUrl->getUrl();
              break;

            case 'large':
              $largeImageUrl = $imageUrl->getUrl();
              break;
          }
        }
      }
    } catch (\Exception $e) {
      \Drupal::logger('lit_cover_service')->error($e->getMessage());
    }

    if (!$largeImageUrl && !$originalImageUrl) {
      \Drupal::messenger()->addMessage('No cover found for ISBN '.$isbn, 'warning');
    }

    // If cover doesn't have a  'large' cover fall back to use the original.
    return $largeImageUrl ?? $originalImageUrl;
  }

  /**
   * Fetch image file and save it to local file system
   *
   * @param string $imageUrl
   *
   * @return \Drupal\file\FileInterface|null
   */
  private function fetchRemoteImageFile(string $imageUrl) {
    try {
      $response = $this->httpClient->request('get', $imageUrl, [
        'headers' => [
          'Referer' => 'https://litteratursiden.dk/',
        ],
      ]);
      $data = $response->getBody()->getContents();
      $dir = self::DRUPAL_FILE_PATH;
      $destination = self::DRUPAL_FILE_PATH . '/' . basename($imageUrl);

      $dirWritable = $this->fileSystem->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY) && $this->fileSystem->prepareDirectory($dir, FileSystemInterface::MODIFY_PERMISSIONS);;
      if (!$dirWritable) {
        throw new DirectoryNotReadyException('Cannot write to: '.$dir);
      }

      if ($data && $this->fileSystem->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY)) {
        $file = file_save_data($data, $destination, FileSystemInterface::EXISTS_REPLACE);
        return (FALSE !== $file) ? $file : NULL;
      }
    } catch (\Exception $e) {
      \Drupal::logger('lit_cover_service')->error($e->getMessage());
    }

    return NULL;
  }

  /**
   * Get Adgangsplatform access token
   *
   * @return string|null
   */
  private function getToken(): ?string {
    // Get Open Platform module settings.
    $config = \Drupal::config('lit_open_platform.settings');

    // Get the Open Platform API client settings.
    $clientId = $config->get('client_id') ?? '';
    $clientSecret = $config->get('client_secret') ?? '';

    // Get access token
    $tokenClient = new TokenClient($clientId, $clientSecret);
    $token = $tokenClient->getAccessToken();

    return $token['access_token'] ?? null;
  }

}
