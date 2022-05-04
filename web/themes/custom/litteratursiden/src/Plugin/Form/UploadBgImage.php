<?php

namespace Drupal\litteratursiden\Plugin\Form;

use Drupal\bootstrap\Plugin\Form\SystemThemeSettings;
use Drupal\bootstrap\Utility\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * @ingroup plugins_form
 *
 * @BootstrapForm("system_theme_settings")
 */
class UploadBgImage extends SystemThemeSettings {

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state, $form_id = NULL) {
    $a =1;
    // Call the parent method from the base theme, if applicable (which it is
    // in this case because Bootstrap actually implements this alter).
    parent::alterForm($form, $form_state, $form_id);

    $form['background'] = [
      '#type' => 'details',
      '#title' => t('Background'),
      '#open' => TRUE,
    ];

    $form['background']['background_path'] = [
      '#type' => 'textfield',
      '#title' => t('Path to custom background image'),
      '#default_value' => theme_get_setting('background_path', 'litteratursiden'),
    ];

    $form['background']['background_upload'] = [
      '#type' => 'file',
      '#title' => t('Upload background image'),
      '#maxlength' => 40,
      '#description' => t("If you don't have direct file access to the server, use this field to upload your background.")
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function validateForm(array &$form, FormStateInterface $form_state) {
    // Handle file uploads.
    $validators = ['file_validate_is_image' => []];

    // Check for a new uploaded background.
    $file = file_save_upload('background_upload', $validators, FALSE, 0);
    if (isset($file)) {
      // File upload was attempted.
      if ($file) {
        // Put the temporary file in form_values so we can save it on submit.
        $form_state->setValue('background_upload', $file);
      }
      else {
        // File upload failed.
        $form_state->setErrorByName('background_upload', t('The background could not be uploaded.'));
      }
    }

    // If the user provided a path for a background file, make sure a file
    // exists at that path.
    if ($form_state->getValue('background_path')) {
      $path = static::validatePath($form_state->getValue('background_path'));
      if (!$path) {
        $form_state->setErrorByName('background_path', t('The custom background path is invalid.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function submitFormElement(Element $form, FormStateInterface $form_state) {
    // This method is automatically called when the form is submitted.
    // Load the file via file.fid.
    $values = $form_state->getValues();

    // If the user uploaded a new background, save it to a permanent location
    // and use it in place of the default theme-provided file.
    if (!empty($values['background_upload'])) {
      $source = $values['background_upload']->getFileUri();
      $file_system = \Drupal::service('file_system');
      $destination = file_build_uri($file_system->basename($source));
      $filename = $file_system->copy($source, $destination);
      $values['background_path'] = $filename;
    }

    unset($values['background_upload']);

    // If the user entered a path relative to the system files directory for
    // a logo or favicon, store a public:// URI so the theme system can handle it.
    if (!empty($values['background_path'])) {
      $values['background_path'] = static::validatePath($values['background_path']);
    }

    $form_state->setValues($values);

    parent::submitFormElement($form, $form_state);
  }

  /**
   * Helper function for the system_theme_settings form.
   *
   * Attempts to validate normal system paths, paths relative to the public files
   * directory, or stream wrapper URIs. If the given path is any of the above,
   * returns a valid path or URI that the theme system can display.
   *
   * @param string $path
   *   A path relative to the Drupal root or to the public files directory, or
   *   a stream wrapper URI.
   * @return mixed
   *   A valid path that can be displayed through the theme system, or FALSE if
   *   the path could not be validated.
   */
  protected static function validatePath($path) {
    // Absolute local file paths are invalid.
    if (\Drupal::service('file_system')->realpath($path) == $path) {
      return FALSE;
    }
    // A path relative to the Drupal root or a fully qualified URI is valid.
    if (is_file($path)) {
      return $path;
    }
    // Prepend 'public://' for relative file paths within public filesystem.
    if (StreamWrapperManager::getScheme($path) === FALSE) {
      $path = 'public://' . $path;
    }
    if (is_file($path)) {
      return $path;
    }
    return FALSE;
  }

}
