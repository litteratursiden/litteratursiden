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
    // @todo Check if reference field exists.
    Drupal::service('path.alias_manager')->getPathByAlias($trimmedUrl);
    $landing_pages = [
      'frontpage' => '/frontpage',
      'boerneboeger' => '/boerneboeger',
      'forfattere' => '/forfattere',
      'temaer' => '/temaer',
    ];
    $nodeTypes = $node_types = \Drupal\node\Entity\NodeType::loadMultiple();

    $count = 0;
    $countFront = 0;
    $broken_links = [];
    $disabled = 0;

    // Get all block entities.
    $blocklist = Drupal::entityQuery('block')->execute();

    // Check for existing landing pages.
    foreach ($landing_pages as $name => $alias) {
      $nodeUrl = Drupal::service('path.alias_manager')->getPathByAlias($alias);
      $urlExploded = explode('/', $nodeUrl);
      if (isset($urlExploded['2']) && is_numeric($urlExploded['2'])) {
        continue;
      }
      else{
        throw new Exception(dt('Missing landing page: !name', ['!name' => $name]));
      }
    }

    // Check for existing reference field on all nodes.
    foreach ($nodeTypes as $name => $type) {
      $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $name);
      if(isset($definitions['field_block_reference'])) {
        continue;
      }
      else{
        throw new Exception(dt('Missing block reference field on: !nodeType', ['!nodeType' => $name]));
      }
    }

    // Handle blocks.
    foreach ($blocklist as $blockname) {
      $blockConfig = Drupal::config('block.block.' . $blockname)->getRawData();
      if ($blockConfig['status'] == FALSE) {
        // Delete disabled blocks config from db.
        $this->deleteConfig($blockConfig);
        $disabled = $disabled + 1;
      }
      else {
        if($blockConfig['settings']['provider'] == 'block_content' && $blockConfig['region'] == 'content' && $blockConfig['theme'] == 'litteratursiden') {
          $urls = explode(PHP_EOL, $blockConfig['visibility']['request_path']['pages']);
          foreach ($urls as $key => $url) {

            // Remove trailing space.
            $trimmedUrl = trim($url);

            switch ($trimmedUrl) {
              case '<front>':
                // Convert to right node.
                $this->handleLandingPages('/frontpage', $blockConfig);
                $countFront = $countFront + 1;
                $this->output()->writeln($countFront);
                break;
              case '/books':
                $this->handleLandingPages($trimmedUrl, $blockConfig);
                break;
              case '/authors':
                $this->handleLandingPages($trimmedUrl, $blockConfig);
                break;
              case '/topics':
                $this->handleLandingPages($trimmedUrl, $blockConfig);
                break;
              case '':
                unset($urls[$key]);
                break;
              default:
                $url = Drupal::service('path.alias_manager')->getPathByAlias($trimmedUrl);
                $urlExploded = explode('/', $url);
                if (isset($urlExploded['2']) && is_numeric($urlExploded['2'])) {
                  // do stuff.
                  $this->addReferenceToNode($urlExploded['2'], $blockConfig);
                }
                else {
                  $broken_links[$url][] = $blockConfig['settings']['label'];
                }
                break;
            }
          }
        }
      }

      $count = $count + 1;

      if ($count > 400) {
         break;
      }

      // Create csv with broken links.
      $this->createBrokenLinksCsv($broken_links);
    }

    $this->output()->writeln('--- Handled blocks ---');
    $this->output()->writeln($count);
    $this->output()->writeln('--- Disabled blocks config deleted ---');
    $this->output()->writeln($disabled);
    $this->output()->writeln('--- FRONT ---');
    $this->output()->writeln($countFront);
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

  private function handleLandingPages($url, $blockConfig) {

  }

  private function addReferenceToNode($nid, $blockConfig) {

  }

  private function deleteConfig($blockConfig) {
    Drupal::configFactory()->getEditable('block.block.' . $blockConfig['id'])->delete();
  }
}
