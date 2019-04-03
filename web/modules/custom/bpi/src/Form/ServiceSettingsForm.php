<?php

namespace Drupal\bpi\Form;

use Bpi\Sdk\Exception\SDKException;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ServiceSettingsForm.
 */
class ServiceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bpi_service_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'bpi.service_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->config('bpi.service_settings');

    $form['bpi_service_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('BPI Service settings'),
    ];

    $form['bpi_service_settings']['bpi_agency_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Agency ID'),
      '#description' => $this->t('The 6-digit code representing the library organization'),
      '#default_value' => $settings->get('bpi_agency_id') ?: '',
      '#required' => TRUE,
    ];

    $form['bpi_service_settings']['bpi_service_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service URL'),
      '#description' => $this->t('The location of the web-service (http://bpi1.inlead.dk)'),
      '#default_value' => $settings->get('bpi_service_url') ?: 'http://bpi1.inlead.dk',
      '#required' => TRUE,
    ];

    $form['bpi_service_settings']['bpi_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret key'),
      '#default_value' => $settings->get('bpi_secret_key') ?: '',
      '#required' => TRUE,
    ];

    $form['bpi_service_settings']['bpi_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public key'),
      '#default_value' => $settings->get('bpi_api_key') ?: '',
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!UrlHelper::isValid($form_state->getValue('bpi_service_url'), TRUE)) {
      $form_state->setErrorByName('bpi_service_url', $this->t('Please enter a valid url.'));
    }
    else {
      /** @var \Drupal\bpi\Services\BpiService $bpi_service */
      $bpi_service = \Drupal::service('bpi.service');

      try {
        $bpi_service->checkConnectivity(
          $form_state->getValue('bpi_service_url'),
          $form_state->getValue('bpi_agency_id'),
          $form_state->getValue('bpi_api_key'),
          $form_state->getValue('bpi_secret_key')
        );
      }
      catch (SDKException $e) {
        $form_state->setErrorByName('', $this->t('Failed to communicate with BPI service.'));
        \Drupal::logger('bpi')->error($e->getMessage());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('bpi.service_settings')
      ->set('bpi_agency_id', $form_state->getValue('bpi_agency_id'))
      ->set('bpi_service_url', $form_state->getValue('bpi_service_url'))
      ->set('bpi_secret_key', $form_state->getValue('bpi_secret_key'))
      ->set('bpi_api_key', $form_state->getValue('bpi_api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
