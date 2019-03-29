<?php

namespace Drupal\lit_xml\Utils;

use Drupal\Core\Config\Config;
use Drupal\lit_xml\TransformerInterface;
use Drupal\node\Entity\Node;

/**
 * Class XMLExporter
 * @package Drupal\lit_xml\Utils
 */
abstract class XmlExporter {

  /**
   * @var string
   */
  protected $filename;

  /**
   * @var Config
   */
  protected $config;

  /**
   * @var \DOMDocument
   */
  protected $dom;

  /**
   * @var string
   */
  protected $format;

  /**
   * @var string
   */
  protected $destination;

  /**
   * @var array
   */
  private $transformers;

  /**
   * @var int
   */
  private $lastRun = 0;

  /**
   * @param string $filename
   * @param string $destination
   * @param \Drupal\Core\Config\Config $config
   */
  public function __construct(string $filename, string $destination, Config $config) {
    $this->filename = $filename;
    $this->destination = $destination;
    $this->config = $config;
    $this->dom = $this->buildDom();
    $this->transformers = $this->getTransformers();
  }

  /**
   * @return string
   */
  public function getFormat(): string {
    return $this->format;
  }

  /**
   * @param string $format
   * @return $this
   */
  public function setFormat(string $format) {
    $this->format = $format;

    return $this;
  }

  /**
   * @return string
   */
  public function getFilename(): string {
    return $this->filename;
  }

  /**
   * @param string $filename
   * @return $this
   */
  public function setFilename(string $filename) {
    $this->filename = $filename;

    return $this;
  }

  /**
   * @return string
   */
  public function getDestination(): string {
    return $this->destination;
  }

  /**
   * @param string $destination
   * @return $this
   */
  public function setDestination(string $destination) {
    $this->destination = $destination;

    return $this;
  }

  /**
   * @return int
   */
  public function getLastRun() {
    if (!$this->lastRun) {
      $query = \Drupal::database()->select('lit_xml_exports', 't');
      $query->addField('t', 'created_at');
      $query->condition('destination', $this->getDestination());
      $query->orderBy('created_at', 'DESC');
      $query->range(0, 1);

      $this->lastRun = $query->execute()->fetchField();

    }

    return $this->lastRun;
  }

  /**
   * @param array $record
   * @return $this
   */
  public function addRecord(array $record) {
    $page = $this->dom->createElement('page');
    $this->dom->documentElement->appendChild($page);

    foreach ($record as $field => $value) {
      if (is_array($value)) {
        $value = implode(';', $value);
      }
      $element = $this->dom->createElement($field);
      $page->appendChild($element);
      $element->appendChild($this->dom->createTextNode($value));
    }

    return $this;
  }

  /**
   * Save xml to file.
   */
  public function save() {
    $this->dom->save($this->filename);
  }

  /**
   * @param array $types Content types such as article.
   * @return mixed
   */
  public function setXmlData(array $types) {
    foreach ($types as $type) {
      if (!$this->exporterIsRunning($type)) {
        $nodes = $this->getNodes($type);

        $transformer = $this->getTransformer($type);
        $records = $transformer ? $transformer::transformCollection($nodes) : [];

        foreach ($records as $record) {
          $this->addRecord($record);
          $this->updateCacheStamp($type);
        }
      }
    }
  }

  public function exporterIsRunning($type) {
    $cid   = "lit_xml:$type:lastUpdated";
    $cache = \Drupal::cache()->get($cid, true);

    return !empty($cache->data);
  }

  public function updateCacheStamp($type) {
    $cid         = "lit_xml:$type:lastUpdated";
    $currentTime = \Drupal::time()->getCurrentTime();
    $expireTime  = strtotime('+5 minutes', $currentTime);

    \Drupal::cache()->set($cid, $currentTime, $expireTime);
  }

  /**
   * @return bool
   */
  public function hasData() {
    return $this->dom->documentElement->hasChildNodes();
  }

  /**
   * @param string $type
   * @return \Drupal\Core\Entity\EntityInterface[]|static[]
   */
  protected function getNodes(string $type) {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', $type);

    if ($this->config->get('type') != 'full') {
      $query->condition('changed', $this->getLastRun(), '>=');
    }

    $nids = $query->execute();

    return Node::loadMultiple($nids);
  }

  /**
   * @param string $name
   * @return \Drupal\lit_xml\TransformerInterface
   */
  protected function getTransformer(string $name): TransformerInterface {
    return $this->transformers[$name] ?? NULL;
  }

  /**
   * @return \DOMDocument
   */
  protected function buildDom(): \DOMDocument {
    $dom = new \DOMDocument("1.0", "ISO-8859-1");

    $records = $dom->createElement('records');
    $dom->appendChild($records);

    $date_attribute = $dom->createAttribute('date');
    $records->appendChild($date_attribute);

    $date = $dom->createTextNode(date('Ymd\THisO', time()));
    $date_attribute->appendChild($date);

    return $dom;
  }

  /**
   * @return array
   */
  abstract protected function getTransformers(): array;

}
