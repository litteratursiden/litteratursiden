<?php

namespace Drupal\lit\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsInterface;
use Drupal\Core\Asset\AttachedAssets;

/**
 * Ajax command for showing form errors through client side validation lib.
 */
class ShowErrorsCommand implements CommandInterface, CommandWithAttachedAssetsInterface {

  /**
   * A jQuery selector string for the form.
   *
   * @var string
   */
  protected string $selector;

  /**
   * Key/value pairs of input names and messages.
   *
   * @var array
   */
  protected array $errors;

  /**
   * Constructs an ShowErrorsCommand object.
   *
   * @param string $selector
   *   A jQuery selector string for the form or
   *   element inside the form for which errors should be shown.
   *   If NULL is given the first form on the page will be taken.
   *   The jQuery validation should be ran on the selector's form.
   *   ClientSide Validation JQuery module provides this
   *   for all forms out of the box.
   * @param array $errors
   *   Array of key/value pairs of input names and messages.
   */
  public function __construct($selector, array $errors) {
    $this->selector = $selector;
    $this->errors = $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    return [
      'command' => 'showFormErrors',
      'selector' => $this->selector,
      'errors' => $this->errors,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttachedAssets(): ?AttachedAssets {
    return new AttachedAssets();
  }

}
