<?php

namespace Drupal\lit\Commands;

use Drupal\block_content\Entity\BlockContent;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drush\Commands\DrushCommands;

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
    $failedNodes = [];
    $count = 0;
    $disabled = 0;
    $handledUrls = 0;

    $this->checkState();

    // Get all block entities.
    $blockEntities = \Drupal::entityQuery('block')->execute();
    // Load all block config.
    foreach ($blockEntities as $blockName) {
      $blockList[] = \Drupal::config('block.block.' . $blockName)->getRawData();
    }

    $noOfBlocks = count($blockList);
    // Sort config by block weight to ensure proper order when migrated.
    usort($blockList, function ($a, $b) {
      return $a['weight'] - $b['weight'];
    });

    // Handle blocks.
    foreach ($blockList as $blockConfig) {
      $broken_links = [];

      if ($blockConfig['status'] == FALSE && $blockConfig['theme'] == 'litteratursiden') {
        // Delete disabled blocks config from db.
        $this->deleteConfig($blockConfig);
        $disabled += 1;
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
            // The old main menu landing pages URLs have changed.
            $trimmedUrl = $this->handleLandingPages($trimmedUrl);

            $url = \Drupal::service('path.alias_manager')->getPathByAlias($trimmedUrl);
            $urlExploded = explode('/', $url);
            if (
              isset($urlExploded['2']) &&
              is_numeric($urlExploded['2']) &&
              $urlExploded['1'] == 'node' &&
              empty($urlExploded['3'])
            ) {
              $result = $this->addReferenceToNode($urlExploded['2'], $blockConfig);
              $failedNodes[$result['status']][] = $result;
            }
            else {
              $broken_links[$url][] = $blockConfig['settings']['label'];
            }
            $this->output()->writeln($trimmedUrl . ' (' . $count . '/' . $noOfBlocks . ')');
            $handledUrls += 1;
          }

          // Delete the configuration.
          $this->deleteConfig($blockConfig);
          $count += 1;
        }

        if ($blockConfig['id'] == 'views_block__recent_reviews_block') {
          // Create views reference spot.
          $block = BlockContent::create([
            'info' => 'Nyeste anmeldelser',
            'type' => 'overview',
            'field_views_reference' => [
              'target_id' => 'recent_reviews',
              'display_id' => 'block_recent_reviews',
              'argument' => NULL,
              'title' => '1',
              'data' => NULL,
            ],
          ]);
          $block->save();
          $url = \Drupal::service('path.alias_manager')->getPathByAlias('/frontpage');
          $urlExploded = explode('/', $url);
          $node = Node::load($urlExploded['2']);
          if (isset($node)) {
            $node->field_block_reference[] = $block->id();
            $node->save();
          }

          $this->deleteConfig($blockConfig);
        }

        // Remove book list view. Rendered from node book-list full template.
        if ($blockConfig['id'] == 'views_block__book_list_books_block') {
          $this->deleteConfig($blockConfig);
        }
        if ($blockConfig['id'] == 'views_block__related_books_by_author_block') {
          $this->deleteConfig($blockConfig);
        }

        // Create csv with broken links.
        $this->createBrokenLinksCsv($broken_links);
      }

      if ($count > $noOfBlocks) {
        break;
      }
    }

    $this->output()->writeln('--- Migrated blocks ---');
    $this->output()->writeln($count);
    $this->output()->writeln('--- Disabled blocks config deleted ---');
    $this->output()->writeln($disabled);
    $this->output()->writeln('--- Handled urls ---');
    $this->output()->writeln($handledUrls);

    \Drupal::state()->set('block_migration_status', 'migrated');
  }

  /**
   * Create CSV with broken links.
   *
   * @param array $broken_links
   *   List of broken links in block.
   */
  private function createBrokenLinksCsv(array $broken_links) {
    $fp = fopen('file.csv', 'a');

    foreach ($broken_links as $key => $fields) {
      array_unshift($fields, $key);
      fputcsv($fp, $fields, ';');
    }

    fclose($fp);
  }

  /**
   * Change incorrect url of landing pages.
   *
   * @param string $url
   *   The url to change.
   *
   * @return string
   *   The new url.
   */
  private function handleLandingPages(string &$url) {
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
    }
    return $url;
  }

  /**
   * Create a block reference on an entity.
   *
   * @param int $nid
   *   A node to add reference to.
   * @param array $blockConfig
   *   The block that holds the old config.
   *
   * @return array
   *   A summary array of action.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function addReferenceToNode(int $nid, array $blockConfig) {
    $blockContentId = explode(':', $blockConfig['settings']['id']);
    $blockContentUuid = $blockContentId['1'];
    $block = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $blockContentUuid);
    if (isset($block)) {
      // Move block label to spotbox block_content if block label
      // display is set.
      if ($blockConfig['settings']['label_display'] == 'visible') {
        if ($block->bundle() == 'spot') {
          $block->field_label = $blockConfig['settings']['label'];
          $block->save();
        }
        if ($block->bundle() == 'book_list_carousel') {
          $block->info = $blockConfig['settings']['label'];
          $block->save();
        }
      }
      $node = Node::load($nid);
      if (isset($node)) {
        $node->field_block_reference[] = $block->id();
        $node->save();
        $status = 'Success';
      }
      else {
        $status = 'Missing node';
      }
    }
    else {
      $status = 'Missing block';
    }

    return ['nid' => $nid, 'config' => $blockConfig, 'status' => $status];
  }

  /**
   * Delete block configuration but maintain block content.
   *
   * @param array $blockConfig
   *   An array containing block configuration.
   */
  private function deleteConfig(array $blockConfig) {
    \Drupal::configFactory()->getEditable('block.block.' . $blockConfig['id'])->delete();
  }

  /**
   * Ensure the needed data components are present.
   *
   * @throws \Exception
   */
  private function checkState() {
    if (\Drupal::state()->get('block_migration_status')) {
      throw new \Exception(dt('Data has already been migrated.'));
    }

    $landing_pages = [
      'frontpage' => '/frontpage',
      'boerneboeger' => '/boerneboeger',
      'forfattere' => '/forfattere',
    ];
    $nodeTypes = NodeType::loadMultiple();

    // Check for existing landing pages.
    foreach ($landing_pages as $name => $alias) {
      $nodeUrl = \Drupal::service('path.alias_manager')->getPathByAlias($alias);
      $urlExploded = explode('/', $nodeUrl);
      if (isset($urlExploded['2']) && is_numeric($urlExploded['2'])) {
        continue;
      }
      else {
        throw new \Exception(dt('Missing landing page: !name', ['!name' => $name]));
      }
    }

    // Check for existing reference field on all nodes.
    foreach ($nodeTypes as $name => $type) {
      $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $name);
      if (isset($definitions['field_block_reference'])) {
        continue;
      }
      else {
        throw new \Exception(dt('Missing block reference field on: !nod  eType', ['!nodeType' => $name]));
      }
    }
  }

}
