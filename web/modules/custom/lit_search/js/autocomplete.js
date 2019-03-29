(function ($, Drupal, drupalSettings) {

  'use strict';

  var length = 3;

  var _delay = function () {
    var timer = 0;
    return function (callback, ms) {
      clearTimeout(timer);
      timer = setTimeout(callback, ms);
    };
  }();

  Drupal.behaviors.lit_search_autocomplete = {
    attach: function attach(context, settings) {
      $('.lit-search-autocomplete-field').on('keyup', function () {
        var el = $(this);
        var totalEl = el.parent().next('.lit-search-autocomplete-total');
        var resultsEl = totalEl.parent().next('.lit-search-autocomplete-results');
        el.parent().find('.loader').remove();
        el.parent().append('<div class="loader"></div>');
        _delay(function () {
          if (el.val() && el.val().length >= length) {
            jQuery.ajax({
              dataType: "json",
              url: drupalSettings.path.baseUrl + "autocomplete",
              data: { q: el.val() },
              success: function success(result) {
                //Remove all other instances
                totalEl.html('');
                resultsEl.html('');
                el.parent().find('.loader').remove();
                el.parent().addClass('active');
                totalEl.html('(' + result.total + ')');
                resultsEl.html('<div class="container">' + result.data + '</div>');
                $(document).click(function(event) {
                  if ( !$(event.target).parents().hasClass('lit-search-autocomplete-results')) {
                    $(document).find('.lit-search-autocomplete-total').html('');
                    $(document).find('.lit-search-autocomplete-results').html('');
                    el.parent().removeClass('active');
                  }
                });
                $('body').on('click', function (e) {
                  if (e.target.id === 'edit-search-results' || e.target.id === 'edit-search-results--2' ) {
                    return false
                  } else {

                  }
                });
              }
            });
          } else {
            el.parent().removeClass('active');
            el.parent().find('.loader').remove();
            totalEl.html('');
            resultsEl.html('');
            $(document).find('#bodyOverlay').remove();
          }
        }, 300);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
