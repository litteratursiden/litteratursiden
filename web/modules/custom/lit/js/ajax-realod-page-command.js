(function ($, Drupal, settings) {

  "use strict";

  /**
   * Ajax command for reloading page.
   *
   * @param {Drupal.Ajax} [ajax]
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} [response]
   *   The response from the Ajax request.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.reloadPage = function (ajax, response, status) {
    location.reload();
  };

})(jQuery, Drupal, drupalSettings);
