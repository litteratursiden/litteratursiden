<?php

namespace Drupal\lit_search;

/**
 * Class SearchGroup.
 *
 * @package Drupal\lit_search
 */
class SearchGroup {

  /**
   * @var string
   */
  private $title;

  /**
   * @var int
   */
  private $count = 0;

  /**
   * @var array
   */
  private $items = [];

  /**
   * SearchGroup constructor.
   *
   * @param string $title
   */
  public function __construct(string $title) {
    $this->title = $title;
  }

  /**
   * @return string
   */
  public function getTitle(): string {
    return $this->title;
  }

  /**
   * @param string $title
   * @return $this
   */
  public function setTitle(string $title) {
    $this->title = $title;

    return $this;
  }

  /**
   * @return int
   */
  public function getCount(): int {
    return $this->count;
  }

  /**
   * @param int $count
   *
   * @return $this
   */
  public function setCount(int $count) {
    $this->count = $count;

    return $this;
  }

  /**
   * @return array
   */
  public function getItems(): array {
    return $this->items;
  }

  /**
   * @param array $items
   *
   * @return $this
   */
  public function setItems(array $items) {
    $this->items = $items;

    return $this;
  }

}
