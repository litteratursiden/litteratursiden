<?php

namespace Drupal\lit\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsInterface;
use Drupal\Core\Asset\AttachedAssets;

/**
 * Ajax command for reloading current page.
 */
class ReloadPageCommand implements CommandInterface, CommandWithAttachedAssetsInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'reloadPage',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttachedAssets() {
    $attach_assets = new AttachedAssets();
    $attach_assets->setLibraries(['lit/reload.page.command']);
    return $attach_assets;
  }

}
