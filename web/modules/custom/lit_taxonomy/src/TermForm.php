<?php

namespace Drupal\lit_taxonomy;

use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\TermForm as BaseTermForm;
use Drupal\taxonomy\TermInterface;

/**
 * Base for handler for taxonomy term edit forms.
 */
class TermForm extends BaseTermForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['#entity_builders']['update_status'] = '::updateStatus';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $taxonomy_term = $this->entity;
    $account = \Drupal::currentUser();

    if ($account->hasPermission('administer taxonomy') || $account->hasPermission("edit terms in {$taxonomy_term->bundle()}")) {
      // Add a "Publish" button.
      $element['publish'] = $element['submit'];
      // If the "Publish" button is clicked, we want to update the status to "published".
      $element['publish']['#published_status'] = TRUE;
      $element['publish']['#dropbutton'] = 'save';
      if ($taxonomy_term->isNew()) {
        $element['publish']['#value'] = t('Save and publish');
      }
      else {
        $element['publish']['#value'] = Term::isPublished($taxonomy_term) ? t('Save and keep published') : t('Save and publish');
      }
      $element['publish']['#weight'] = 0;

      // Add a "Unpublish" button.
      $element['unpublish'] = $element['submit'];
      // If the "Unpublish" button is clicked, we want to update the status to "unpublished".
      $element['unpublish']['#published_status'] = FALSE;
      $element['unpublish']['#dropbutton'] = 'save';
      if ($taxonomy_term->isNew()) {
        $element['unpublish']['#value'] = t('Save as unpublished');
      }
      else {
        $element['unpublish']['#value'] = !Term::isPublished($taxonomy_term) ? t('Save and keep unpublished') : t('Save and unpublish');
      }
      $element['unpublish']['#weight'] = 10;

      // If already published, the 'publish' button is primary.
      if (Term::isPublished($taxonomy_term)) {
        unset($element['unpublish']['#button_type']);
      }
      // Otherwise, the 'unpublish' button is primary and should come first.
      else {
        unset($element['publish']['#button_type']);
        $element['unpublish']['#weight'] = -10;
      }

      // Remove the "Save" button.
      $element['submit']['#access'] = FALSE;
    }

    $element['delete']['#access'] = $taxonomy_term->access('delete');
    $element['delete']['#weight'] = 100;

    return $element;
  }


  /**
   * Entity builder updating the term status with the submitted value.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param TermInterface $term
   *   The node updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\node\NodeForm::form()
   */
  public function updateStatus($entity_type_id, TermInterface $term, array $form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $term->set('status', $element['#published_status']);
    }
  }

}
