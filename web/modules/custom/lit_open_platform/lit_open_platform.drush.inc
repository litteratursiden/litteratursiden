<?php

use Drupal\lit_open_platform\Api\SearchClient;
use Drupal\node\Entity\Node;
use Drupal\lit_open_platform\Transformers\WorkTransformer;

/**
 * Implements hook_drush_command().
 */
function lit_open_platform_drush_command() {
  $commands['lit-fill-out-pid'] = [
    'description' => 'This command fills out missing PIDs on books based on ISBN.',
    'options' => [
      'offset' => 'The offset of books.',
      'limit' => 'The limit of books.',
    ],
    'aliases' => ['lit-fop'],
  ];

  $commands['lit-sync-books'] = [
    'description' => 'This command fills out missing fields on books.',
    'options' => [
      'fields' => 'Fields that need to be synced. By default only field_book_general_tags will sync.',
      'delta' => 'Sync only the books for the last N days.',
      'offset' => 'The offset of books.',
      'limit' => 'The limit of books.',
    ],
    'aliases' => ['lit-sb'],
  ];

  return $commands;
}

/**
 * Implements drush_[MODULE_NAME]_[COMMAND_NAME]().
 */
function drush_lit_open_platform_lit_fill_out_pid() {
  $offset = drush_get_option('offset') ?: 0;

  // Retrieve books count with missing pid.
  $query = \Drupal::database()->select('node', 'n');
  $query->addExpression('COUNT(*)', 'count');
  $query->innerJoin('node__field_book_isbn', 'isbn', 'isbn.entity_id = n.nid');
  $query->leftJoin('node__field_book_pid', 'pid', 'pid.entity_id = n.nid');
  $query->condition('n.type', 'book');
  $query->isNULL('pid.entity_id');
  $query->isNotNull('isbn.entity_id');

  $books_count = $query->execute()->fetchField();
  $books_count -= $offset;
  $books_count = $books_count < 0 ? 0 : $books_count;

  $limit = drush_get_option('limit');
  if (!$limit || $books_count < $limit) {
    $limit = $books_count;
  }

  drush_print(dt('The @count missing PIDs have found.', ['@count' => $books_count]));

  if ($books_count) {
    // Get settings.
    $config = \Drupal::config('lit_open_platform.settings');

    // Get the open platform API client.
    $client_id = $config->get('client_id') ?? '';
    $client_secret = $config->get('client_secret') ?? '';

    $client = SearchClient::getInstance($client_id, $client_secret);

    $operations = [
      ['lit_open_platform_batch_fill_pid_process', [$offset, $limit, $client]]
    ];
    $batch = [
      'operations' => $operations,
      'finished' => 'lit_open_platform_batch_fill_pid_finished',
      'title' => t('Import missing PIDs'),
      'init_message' => t('Starting import pids.....'),
      'progress_message' => t('Completed @current step of @total.'),
      'error_message' => t('Import pids deletion has encountered an error.'),
    ];

    // Initialize the batch.
    batch_set($batch);

    // Start the batch process.
    drush_backend_batch_process();
  }
}

/**
 * @param $offset
 * @param $limit
 * @param SearchClient $client
 * @param $context
 */
function lit_open_platform_batch_fill_pid_process($offset, $limit, $client, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_node'] = 0;
    $context['sandbox']['max'] = $limit;
  }

  $query = \Drupal::database()->select('node', 'n');
  $query->addField('n', 'nid');
  $query->addField('isbn', 'field_book_isbn_value', 'isbn');
  $query->innerJoin('node__field_book_isbn', 'isbn', 'isbn.entity_id = n.nid');
  $query->leftJoin('node__field_book_pid', 'pid', 'pid.entity_id = n.nid');
  $query->condition('n.type', 'book');
  $query->condition('n.nid', $context['sandbox']['current_node'], '>');
  $query->isNULL('pid.entity_id');
  $query->orderBy('n.nid', 'ASC');
  $query->range($offset, 50);

  $books = $query->execute()->fetchAll();

  foreach ($books as $book) {
    if ($pid = $client->requestPidByIsbn($book->isbn)) {
      $node = Node::load($book->nid);
      $node->set('field_book_pid', $pid);
      $node->save();
    }

    $context['results'][] = $book->nid;

    // Update our progress information.
    $context['sandbox']['progress']++;
    $context['sandbox']['current_node'] = $book->nid;

    if( NULL !== $pid) {
        $context['message'] = t('Book @iterator with nid: @nid and ISBN: @isbn has been processed... We found pid: @pid ', [
            '@iterator' => $context['sandbox']['progress'],
            '@nid' => $book->nid,
            '@isbn' => $book->isbn,
            '@pid' => $pid
        ]);
    } else {
        $context['message'] = t('Book @iterator with nid: @nid and ISBN: @isbn has been processed... No pid found ', [
            '@iterator' => $context['sandbox']['progress'],
            '@nid' => $book->nid,
            '@isbn' => $book->isbn
        ]);
    }
  }

  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

/**
 * @param $success
 * @param $results
 * @param $operations
 */
function lit_open_platform_batch_fill_pid_finished($success, $results, $operations) {
  if ($success) {
    drush_print(dt('@count books have successfully been processed.', ['@count' => count($results)]));
  }
}


/**
 * Implements drush_[MODULE_NAME]_[COMMAND_NAME]().
 * example: ../vendor/bin/drush lit-sync-books --fields=field_book_general_tags,title
 */
function drush_lit_open_platform_lit_sync_books() {
  // Get module settings.
  $settings = \Drupal::config('lit_open_platform.settings');

  // Fields to sync.
  $fields = [
    'title',
    'field_book_body',
    'field_book_general_tags',
    'field_book_author',
    'field_book_reference_author',
    'field_book_subject_number',
    'field_book_language',
    'field_book_number_of_pages',
    'field_book_published_year',
    'field_book_publisher',
    'field_book_cover_image',
    'field_book_translater',
    'field_book_old_library_key'
  ];

  // Replace default fields with user fields.
  if ($user_fields = drush_get_option('fields')) {
    $fields = explode(',', preg_replace('/\s+/', '', $user_fields));
  }

  $delta = drush_get_option('delta');
  $delta_timestamp = is_numeric($delta) ? time() - $delta * 60 * 60 * 24 : FALSE;

  // Get last sync book id.
  $last_sync_nid = $settings->get('last_sync_nid') ?: 0;
  $offset = drush_get_option('offset') ?: 0;

  // Retrieve books count with pid.
  $query = \Drupal::database()->select('node_field_data', 'n');
  $query->addExpression('COUNT(*)', 'count');
  $query->condition('n.type', 'book');

  if ($delta_timestamp) {
    $condition = $query->orConditionGroup()
      ->condition('n.created', $delta_timestamp, '>')
      ->condition('n.changed', $delta_timestamp, '>');

    $query->condition($condition);
  }
  else {
    $query->condition('n.nid', $last_sync_nid, '>');
  }

  $query->innerJoin('node__field_book_pid', 'pid', 'pid.entity_id = n.nid');
  $books_count = $query->execute()->fetchField();
  $books_count -= $offset;
  $books_count = $books_count < 0 ? 0 : $books_count;

  $limit = drush_get_option('limit');
  if (!$limit || $books_count < $limit) {
    $limit = $books_count;
  }

  drush_print(dt('There are @count unprocessed books with pid.', ['@count' => $books_count]));

  if ($books_count) {
    // Get the open platform API client.
    $client_id = $settings->get('client_id') ?? '';
    $client_secret = $settings->get('client_secret') ?? '';
    $client = SearchClient::getInstance($client_id, $client_secret);

    $operations = [
      ['lit_open_platform_batch_sync_books_process', [$fields, $offset, $limit, $last_sync_nid, $delta_timestamp, $client]]
    ];
    $batch = [
      'operations' => $operations,
      'finished' => 'lit_open_platform_batch_sync_books_finished',
      'title' => t('Import missing data'),
      'init_message' => t('Starting import data.....'),
      'progress_message' => t('Completed @current step of @total.'),
      'error_message' => t('Import data deletion has encountered an error.'),
    ];

    // Initialize the batch.
    batch_set($batch);

    // Start the batch process.
    drush_backend_batch_process();
  }
}

/**
 * @param $fields
 * @param $offset
 * @param $limit
 * @param $last_sync_nid
 * @param SearchClient $client
 * @param $context
 */
function lit_open_platform_batch_sync_books_process($fields, $offset, $limit, $last_sync_nid, $delta, $client, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_node'] = 0;
    $context['sandbox']['max'] = $limit;
  }

  if ($context['sandbox']['current_node'] < $last_sync_nid) {
    $context['sandbox']['current_node'] = $last_sync_nid;
  }

  // Select portion of books.
  $query = \Drupal::database()->select('node_field_data', 'n');
  $query->addField('n', 'nid');
  $query->addField('pid', 'field_book_pid_value', 'pid');
  $query->innerJoin('node__field_book_pid', 'pid', 'pid.entity_id = n.nid');
  $query->condition('n.type', 'book');
  if ($delta) {
    $condition = $query->orConditionGroup()
      ->condition('n.created', $delta, '>')
      ->condition('n.changed', $delta, '>');
    $query->condition($condition);
  }
  else {
    $query->condition('n.nid', $context['sandbox']['current_node'], '>');
  }

  $offset = $context['sandbox']['current_node'] > 0 ? 0 : $offset;
  $query->range($offset, 20);

  $query->orderBy('n.nid', 'ASC');

  $result = $query->execute()->fetchAll();

  $nids = array_map(function ($book) {
    return $book->nid;
  }, $result);

  $pids = array_map(function ($book) {
    return $book->pid;
  }, $result);

  $books = Node::loadMultiple($nids);
  $open_platform_result = $client->requestWork($pids);
  $open_platform_books = WorkTransformer::transformCollectionAndGroupBy('pid', $open_platform_result);

  // Update book data.
  foreach ($books as $book) {
    $open_platform_book = (object) $open_platform_books[$book->field_book_pid->value] ?? FALSE;
    if ($open_platform_book) {
      foreach ($fields as $field) {

        // guard for empty fields
        if (NULL === $open_platform_book->$field) {
            continue;
        }

        // Get current field values.
        $values = $book->get($field)->getValue();

        // Unique logic for tags field.
        if ($field == 'field_book_general_tags') {
          // be sure we have arrays to merge
          $values = is_array($values) ? $values : [];
          $open_platform_book->$field = is_array($open_platform_book->$field) ? $open_platform_book->$field : [];

          // Merge tags.
          $merged_tags = array_unique(array_merge($values, $open_platform_book->$field), SORT_REGULAR);

          // Set only unique tags.
          $book->set($field, $merged_tags);
          $diff = count($merged_tags)-count($values);
          if ($diff > 0) {
            drush_print('Updating book ' . $book->title->value . '('. (string)$book->nid->value . ') ' . $field . ' with ' . $diff . ' new tags');
          }
          continue;
        }

        // Set other fields if not defined already in db.
        if (!$values && $open_platform_book->$field) {
          drush_print('Updating book ' . $book->title->value . '('. (string)$book->nid->value . ') ' . $field . ' with ' . (string)$open_platform_book->$field);
          $book->set($field, $open_platform_book->$field);
        }
      }

      drush_print('Saving book ' . $book->title->value . '('. (string)$book->nid->value . ')');
      $book->save();
    }

    $context['results'][] = $book->id();

    // Update our progress information.
    $context['sandbox']['progress']++;
    $context['sandbox']['current_node'] = $book->id();
    $context['message'] = t('The @iterator book ('. $book->id() .') is processing...', [
      '@iterator' => $context['sandbox']['progress']
    ]);
  }

  // Save last sync book id.
  \Drupal::service('config.factory')
    ->getEditable('lit_open_platform.settings')
    ->set('last_sync_nid', $context['sandbox']['current_node'])
    ->save();

  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

/**
 * @param $success
 * @param $results
 * @param $operations
 */
function lit_open_platform_batch_sync_books_finished($success, $results, $operations) {
  if ($success) {
    drush_print(dt('The @count books successfully processed.', ['@count' => count($results)]));
  }
}
