<?php

namespace Drupal\lit_search;

/**
 * Class for Group Search.
 *
 * @package Drupal\lit_search
 */
class SearchGroup {

  /**
   * The title.
   *
   * @var string
   */
  private $title;

  /**
   * The count.
   *
   * @var int
   */
  private $count = 0;

  /**
   * A list of items.
   *
   * @var array
   */
  private $items = [];

  /**
   * SearchGroup constructor.
   *
   * @param string $title
   *   A title.
   */
  public function __construct(string $title) {
    $this->title = $title;
  }

  /**
   * Get method for title.
   *
   * @return string
   *   The title
   */
  public function getTitle(): string {
    return $this->title;
  }

  /**
   * Set method for title.
   *
   * @param string $title
   *   The title.
   *
   * @return $this
   *   The class with title included.
   */
  public function setTitle(string $title): static {
    $this->title = $title;

    return $this;
  }

  /**
   * Get method for count.
   *
   * @return int
   *   The count.
   */
  public function getCount(): int {
    return $this->count;
  }

  /**
   * Set method for count.
   *
   * @param int $count
   *   The count.
   *
   * @return $this
   *   The class with count included.
   */
  public function setCount(int $count): static {
    $this->count = $count;

    return $this;
  }

  /**
   * Get a list of items.
   *
   * @return array
   *   A list of items.
   */
  public function getItems(): array {
    return $this->items;
  }

  /**
   * Set method for items.
   *
   * @param array $items
   *   A list of items.
   *
   * @return $this
   *   The class with items included.
   */
  public function setItems(array $items): static {
    $this->items = $items;

    return $this;
  }

}
