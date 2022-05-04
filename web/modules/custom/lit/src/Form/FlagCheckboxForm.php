<?php

namespace Drupal\lit\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\flag\Entity\Flag;
use Drupal\flag\FlagInterface;
use Drupal\Core\Entity\Entity;

/**
 * Builds form with checkbox for flagging content.
 */
class FlagCheckboxForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'flag_checkbox';
  }

  /**
   * {@inheritdoc}
   *
   * @param FlagInterface $flag
   *   Flag entity.
   * @param \Drupal\Core\Entity\EntityBase $entity
   *   Flaggable entity.
   */
  public function buildForm(array $form, FormStateInterface $form_state, FlagInterface $flag = NULL, \Drupal\Core\Entity\EntityBase $entity = NULL) {
    if (!$flag || !$entity) {
      return $form;
    }

    // Caching form causes bug with ajax response when you click on the
    // checkbox second time. That's why it's disabled for this form.
    //$form_state->disableCache();

    $form['flag_id'] = [
      '#type' => 'hidden',
      '#value' => $flag->id(),
    ];

    $form['entity_id'] = [
      '#type' => 'hidden',
      '#value' => $entity->id(),
    ];

    $form['entity_type'] = [
      '#type' => 'hidden',
      '#value' => $entity->getEntityTypeId(),
    ];

    $form['is_flagged'] = [
      '#type' => 'checkbox',
      '#title' => $flag->label(),
      '#ajax' => [
        'callback' => '::submitForm',
      ],
      '#default_value' => $flag->isFlagged($entity),
      '#attributes' => [
        'class' => ["flag-checkbox"],
      ],
      '#id' => "flag-checkbox-{$flag->id()}-{$entity->id()}",
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Use values from user input because in form state might be invalid values
    // when two or more forms are presented on the page.
    $input = $form_state->getUserInput();
    $ajax_response = new AjaxResponse();

    /** @var \Drupal\flag\FlagServiceInterface $flag_service */
    $flag_service = \Drupal::service('flag');
    $flag = Flag::load($input['flag_id']);

    $entity_storage = \Drupal::entityTypeManager()->getStorage($input['entity_type']);
    $entity = $entity_storage->load($input['entity_id']);

    try {
      $input['is_flagged'] ? $flag_service->flag($flag, $entity) : $flag_service->unflag($flag, $entity);
    }
    catch (\LogicException $e) {
      // In case of some error we need to make sure that the entity is flagged.
      // If it's not we'll set up the real state of the checkbox on the form.
      $is_flagged = $flag->isFlagged($entity);
      if ($input['is_flagged'] != $is_flagged) {
        $ajax_response->addCommand(new InvokeCommand("#flag-checkbox-{$flag->id()}-{$entity->id()}", 'prop', ['checked', (int) $is_flagged]));
      }
    }

    $flag_selector = ".flag-{$flag->id()}-{$entity->id()}";
    $ajax_response->addCommand(new InvokeCommand($flag_selector, 'removeClass', ['action-unflag action-flag']));
    $ajax_response->addCommand(new InvokeCommand($flag_selector, 'addClass', [$flag->isFlagged($entity) ? 'action-unflag' : 'action-flag']));

    return $ajax_response;
  }

}
