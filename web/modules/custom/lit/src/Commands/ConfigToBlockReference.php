<?php

namespace Drupal\lit\Commands;

use Drupal;
use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;
use Exception;

/**
 * Migrate config to block references.
 */
class ConfigToBlockReference extends DrushCommands {

  /**
   * Migrates data from taxonomy to node.
   *
   * @command config_to_reference:migrate
   * @aliases config-to-reference
   *
   * @throws \Exception
   */
  public function migrate() {
    $broken_links = [];
    $failedNodes = [];
    $count = 0;
    $disabled = 0;
    $handledUrls = 0;

    $this->checkState();

    // Get all block entities.
    $blockEntities = Drupal::entityQuery('block')->execute();
    // Load all block config.
    foreach ($blockEntities as $blockName) {
      $blockList[] = Drupal::config('block.block.' . $blockName)->getRawData();
    }

    // Sort config by block weight.
    usort($blockList, function($a, $b) {
      return $a['weight'] - $b['weight'];
    });

    // Handle blocks.
    foreach ($blockList as $blockConfig) {
      if ($blockConfig['status'] == FALSE) {
        // Delete disabled blocks config from db.
        $this->deleteConfig($blockConfig);
        $disabled = $disabled + 1;
      }
      else {
        if (
          $blockConfig['settings']['provider'] == 'block_content' &&
          $blockConfig['region'] == 'content' &&
          $blockConfig['theme'] == 'litteratursiden'
        ) {
          // Get each request path.
          $urls = explode(PHP_EOL, $blockConfig['visibility']['request_path']['pages']);
          foreach ($urls as $key => $url) {
            // Remove trailing space.
            $trimmedUrl = trim($url);

            // The old main menu landing pages URLS have changed.
            $trimmedUrl = $this->handleLandingPages($trimmedUrl);

            $url = Drupal::service('path.alias_manager')->getPathByAlias($trimmedUrl);
            $urlExploded = explode('/', $url);
            if (isset($urlExploded['2']) && is_numeric($urlExploded['2'])) {
              // Do stuff.
              $failedNodes = $this->addReferenceToNode($urlExploded['2'], $blockConfig);
            }
            else {
              $broken_links[$url][] = $blockConfig['settings']['label'];
            }
            $this->output()->writeln($trimmedUrl);
            $handledUrls = $handledUrls + 1;
          }
        }
      }

      $count = $count + 1;

      if ($count > 100) {
         break;
      }

      // Create csv with broken links.
      $this->createBrokenLinksCsv($broken_links);
    }

    $this->output()->writeln('--- Handled blocks ---');
    $this->output()->writeln($count);
    $this->output()->writeln('--- Disabled blocks config deleted ---');
    $this->output()->writeln($disabled);
    $this->output()->writeln('--- Handled urls ---');
    $this->output()->writeln($handledUrls);
  }

  /**
   * Create CSV with broken links.
   *
   * @param $broken_links
   */
  private function createBrokenLinksCsv($broken_links) {
    $fp = fopen('file.csv', 'w');

    foreach ($broken_links as $key => $fields) {
      array_unshift($fields, $key);
      fputcsv($fp, $fields, ';');
    }

    fclose($fp);
  }

  /**
   * Change incorrect url of landing pages.
   *
   * @param $url
   *
   * @return string
   */
  private function handleLandingPages(&$url) {
    switch ($url) {
      case '<front>':
        $url = '/frontpage';
        break;
      case '/books':
        $url = '/boerneboeger';
        break;
      case '/authors':
        $url = '/forfattere';
        break;
      case '/topics':
        $url = '/temaer';
        break;
    }
    return $url;
  }

  /**
   * Create a block reference on an entity.
   *
   * @param $nid
   * @param $blockConfig
   *
   * @return array
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function addReferenceToNode($nid, $blockConfig) {
    $blockContentId = explode(':', $blockConfig['settings']['id']);
    $blockContentUuid = $blockContentId['1'];
    $block = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $blockContentUuid);
    if (isset($block)) {
      $node = Node::load($nid);
      $node->field_block_reference[] = $block->id();
      $node->save();
      return ['Success' => [$nid => ['nid' => $nid, 'config' => $blockConfig, 'status' => 'Success']]];
    }
    else {
      return ['Missing block' => [$nid => ['nid' => $nid, 'config' => $blockConfig, 'status' => 'Missing block']]];
    }
  }

  /**
   * Delete block configuration but maintain block content.
   *
   * @param $blockConfig
   */
  private function deleteConfig($blockConfig) {
    Drupal::configFactory()->getEditable('block.block.' . $blockConfig['id'])->delete();
  }

  /**
   * Ensure the needed data components are present.
   *
   * @throws \Exception
   */
  private function checkState() {
    $landing_pages = [
      'frontpage' => '/frontpage',
      'boerneboeger' => '/boerneboeger',
      'forfattere' => '/forfattere',
      'temaer' => '/temaer',
    ];
    $nodeTypes = $node_types = \Drupal\node\Entity\NodeType::loadMultiple();

    // Check for existing landing pages.
    foreach ($landing_pages as $name => $alias) {
      $nodeUrl = Drupal::service('path.alias_manager')->getPathByAlias($alias);
      $urlExploded = explode('/', $nodeUrl);
      if (isset($urlExploded['2']) && is_numeric($urlExploded['2'])) {
        continue;
      }
      else {
        throw new Exception(dt('Missing landing page: !name', ['!name' => $name]));
      }
    }

    // Check for existing reference field on all nodes.
    foreach ($nodeTypes as $name => $type) {
      $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $name);
      if(isset($definitions['field_block_reference'])) {
        continue;
      }
      else {
        throw new Exception(dt('Missing block reference field on: !nodeType', ['!nodeType' => $name]));
      }
    }
  }
}
