<?php

/**
 * @file
 * Contains lit_views.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function lit_views_views_data_alter(array &$data) {
  $data['node_field_data']['type']['filter']['id'] = 'lit_bundle';
  $data['node']['lit_comments_count'] = [
    'title' => t('Comments count'),
    'field' => [
      'title' => t('Comments count'),
      'help' => t('Comments count.'),
      'id' => 'lit_comments_count',
    ],
  ];
}
