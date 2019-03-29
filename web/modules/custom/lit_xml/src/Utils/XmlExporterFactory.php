<?php

namespace Drupal\lit_xml\Utils;

use Drupal\lit_xml\Utils\DkAbm\DkXmlExporter;
use Drupal\lit_xml\Utils\DocBook\DocXmlExporter;
use Drupal\lit_xml\Utils\Ws\WsXmlExporter;

/**
 * Class XmlExporterFactory
 * @package Drupal\lit_xml\Utils
 */
class XmlExporterFactory {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  public function __construct() {
    $this->config = \Drupal::configFactory()->getEditable('lit_xml.settings');
  }

  /**
   * @param string $type
   * @param string $destination
   * @param string $filename
   * @return \Drupal\lit_xml\Utils\XmlExporter|null
   */
  public function createXmlExporter(string $type, string $destination, string $filename) {
    $exporter = NULL;

    switch ($type) {
      case 'abm':
        $exporter = new DkXmlExporter($filename, $destination, $this->config);
        $exporter->setFormat($type);
        break;

      case 'docbook':
        $exporter =  new DocXmlExporter($filename, $destination, $this->config);
        $exporter->setFormat($type);
        break;

      case 'ws':
        $exporter = new WsXmlExporter($filename, $destination, $this->config);
        $exporter->setFormat($type);
        break;

      default:
        break;
    }

    return $exporter;
  }

}
