<?php

/**
 * @file
 * Integration with form system.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function lit_user_form_user_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  $param = \Drupal::request()->query->all();
  if (!empty($param['first_time_logging'])) {
    $form['actions']['submit']['#submit'][] = 'lit_user_form_user_form_first_time_logging';
  }
}

/**
 * Submit handler for first time logging.
 */
function lit_user_form_user_form_first_time_logging($form, FormStateInterface $form_state) {
  $form_state->setRedirect('<front>');
  \Drupal::messenger()->addMessage('You\'re log in.');
}
