<?php

namespace Drupal\lit_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the search form.
 */
class SearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lit_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Set method as GET.
    $form_state->setMethod('GET');

    // Rebuild and always process form.
    $form_state->setRebuild(TRUE);
    $form_state->setAlwaysProcess(TRUE);

    // Disable cache.
    $form['#cache'] = ['max-age' => 0];

    $form['search'] = [
      '#type' => 'textfield',
      '#default_value' => $this->getRequest()->get('search'),
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('search')) < 3) {
      $form_state->setErrorByName('search', $this->t('The search text is too short.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //
  }

}
