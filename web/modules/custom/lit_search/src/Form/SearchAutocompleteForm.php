<?php

namespace Drupal\lit_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the search autocomplete form.
 */
class SearchAutocompleteForm extends FormBase {

  /**
   * @var \Symfony\Component\HttpFoundation\ParameterBag
   */
  protected $query;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->query = \Drupal::request()->query;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lit_search_autocomplete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lit-search-autocomplete-field-container'],
      ],
    ];

    $form['search']['autocomplete'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => ['lit-search-autocomplete-field'],
        'placeholder' => $this->t('Enter keyword, book author'),
        'autocomplete' => 'off',
      ],
      '#attached' => [
        'library' => ['lit_search/lit_search.autocomplete'],
      ],
      '#suffix' => '<span class="lit-search-autocomplete-total"></span>',
    ];

    $form['search_results'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lit-search-autocomplete-results'],
      ],
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
    if (strlen($form_state->getValue('autocomplete')) < 3) {
      $form_state->setErrorByName('autocomplete', $this->t('The search text is too short.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->query->set('search', $form_state->getValue('autocomplete'));

    $form_state->setMethod('GET');
    $form_state->setRedirect('view.search.search', [], ['query' => $this->query->all()]);
  }

}
