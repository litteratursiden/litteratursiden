<?php

namespace Drupal\lit_cover_service\Service;

use Drupal\file\FileInterface;

/**
 * Class CoverService.
 */
interface CoverServiceInterface {

  /**
   * Get cover image from ISBN.
   *
   * @param string $isbn
   *   An isbn number.
   *
   * @return \Drupal\file\FileInterface|null
   *   The file.
   */
  public function getCoverImage(string $isbn): ?FileInterface;

}
