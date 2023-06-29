<?php

namespace Drupal\lit_open_platform\Form;

use Drupal\block_content\BlockContentForm;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lit_open_platform\Api\SearchClient as Client;
use Drupal\lit_open_platform\Transformers\WorkTransformer;
use Drupal\node\Entity\Node;

/**
 * Form handler for the node edit forms.
 */
class LitBlockContentForm extends BlockContentForm {

  /**
   * The client instance.
   *
   * @var \Drupal\lit_open_platform\Api\SearchClient
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);

    // Get module config.
    $config = \Drupal::config('lit_open_platform.settings');

    // Get the open platform API client.
    $client_id = $config->get('client_id') ?? '';
    $client_secret = $config->get('client_secret') ?? '';
    $this->client = Client::getInstance($client_id, $client_secret);
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $fields = $this->getFieldsWithBookReference($entity->getFieldDefinitions());
    $values = $form_state->getValues();

    foreach ($fields as $field) {
      // Get the open platform pids.
      $pids = $this->getPids($values[$field]);

      if ($pids && $nids = $this->createBooksFromPids($pids)) {
        foreach ($nids as $index => $nid) {
          $values[$field][$index]['target_id'] = $nid;
        }
      }
    }

    $form_state->setValues($values);

    parent::copyFormValuesToEntity($entity, $form, $form_state);
  }

  /**
   * Create new books from the open platform.
   *
   * @param array $pids
   *
   * @return array
   *
   * @throws \Exception
   */
  protected function createBooksFromPids(array $pids): array {
    $nids = [];

    $transaction = \Drupal::database()->startTransaction();

    try {
      // Load books from the open platform.
      $books = $this->client->requestWork(array_values($pids));

      foreach ($books as $book) {
        if (isset($book['pid']) === FALSE) {
          continue;
        }

        $book = WorkTransformer::transform($book);

        // Get index of the book in the list.
        $index = array_search($book['pid'], $pids);

        // Check if the book exist in the database.
        if ($nid = $this->getBookByPid($book['pid'])) {
          $nids[$index] = $nid;
          continue;
        }

        // Create book if does not exist.
        $node = Node::create($book);

        if ($node->save()) {
          $nids[$index] = $node->id();
        }
      }
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      throw $e;
    }

    return $nids;
  }

  /**
   * Get nid of the book by the pid.
   *
   * @param string $pid
   *
   * @return int|bool
   */
  protected function getBookByPid(string $pid) {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'book')
      ->condition('field_book_pid.value', $pid);

    $nids = $query->execute();

    return $nids ? reset($nids) : FALSE;
  }

  /**
   * Get the open platform pids.
   *
   * @param array $values
   *
   * @return array
   */
  private function getPids(array $values): array {
    $pids = [];

    foreach ($values as $i => $value) {
      if (is_int($i) && preg_match('/^\d+-\w+:(\d|_)+$/', $value['target_id'])) {
        $pids[$i] = $value['target_id'];
      }
    }

    return $pids;
  }

  /**
   * Get fields name that has reference to the book content type.
   *
   * @param $field_definitions
   *
   * @return array
   */
  protected function getFieldsWithBookReference($field_definitions) {
    $result = [];
    foreach ($field_definitions as $field_definition) {
      if ($field_definition->getType() == 'entity_reference') {
        $settings = $field_definition->getSettings();

        if ($settings['handler'] == 'default:node') {
          $target_bundles = $settings['handler_settings']['target_bundles'];

          if (in_array('book', $target_bundles)) {
            $result[] = $field_definition->getName();
          }
        }
      }
    }

    return $result;
  }

}
