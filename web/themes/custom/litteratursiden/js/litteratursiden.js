(function ($) {

  Drupal.behaviors.Litteratursiden = {
    attach: function (context, settings) {

      'use strict';

      $('.book-analysis .content > .form-type-item').insertAfter('.book-analysis .review-author .user-roles');

      jQuery.fn.exists = function () {
        return jQuery(this).length > 0;
      };
      jQuery.fn.existsWithValue = function () {
        return this.length && this.val().length;
      };
      // IE object fit fix for slider
      var uA = window.navigator.userAgent,
          isIE = /msie\s|trident\/|edge\//i.test(uA) && !!(document.uniqueID || document.documentMode || window.ActiveXObject || window.MSInputMethodContext),
          checkVersion = isIE && +/(edge\/|rv:|msie\s)([\d.]+)/i.exec(uA)[2] || NaN;
      if (!isNaN(checkVersion)) {
        $('body').addClass('ie');
        $('#navbar .navbar-header .profile-toggle .wrap').each(function () {
          var $container = $(this),
              imgUrl = $container.find('img').prop('src');
          if (imgUrl) {
            $container.css('backgroundImage', 'url(' + imgUrl + ')').addClass('compat-object-fit');
          }
        });
      }

      // Trim comments after ajax
      function commentsTrim() {
        $(document).find('.comments-body .comment .comment-body-wrapper').each(function (index, element) {
          var commentBodyHeight = $(element).find('.field--name-comment-body').height();
          if (commentBodyHeight > 78) {
            $(element).removeClass('no-btn');
          }
        });
      }

      // Reposition for counter for comments
      $('.comments-body .book-dabate-block .views-field-lit-comments-count').appendTo('.block-views-blockcomments-book-dabate-block  .comments-header .title-container');
      $('.comments-body .user-reviews-block .views-field-lit-comments-count').appendTo('.block-views-blockcomments-user-reviews-block .comments-header .title-container');
      $('.comments-body .comments-block .views-field-comment-count').appendTo('.block-views-blockcomments-comments-block .comments-header .title-container');

      commentsTrim();

      $(window).resize(function () {
        commentsTrim();

        if ($('.block-type-book_list_carousel .node-all').css('display') == 'none') {
            $('.block-type-book_list_carousel:last .node-all').css('display', 'inline-block');
        }
      });

      if ($(document).find('.form-control.error').exists()) {
        $(this).parents('.form-item').addClass('error');
      }

      $(document).once('Litteratursiden').each(function () {

        $(document).on('click', '.comment .comment-body-wrapper .show-more, .comment .comment-body-wrapper .show-less', function () {
            $(this).parent().toggleClass('show');
        });

        // Block scroll function
        var Scroll = function () {
          var x, y;

          function hndlr() {
            window.scrollTo(x, y);
          }

          return {
            disable: function disable(x1, y1) {
              x = x1;
              y = y1;
              if (window.addEventListener) {
                window.addEventListener("scroll", hndlr);
              }
              else {
                window.attachEvent("onscroll", hndlr);
              }
            },
            enable: function enable() {
              if (window.removeEventListener) {
                window.removeEventListener("scroll", hndlr);
              }
              else {
                window.detachEvent("onscroll", hndlr);
              }
            }
          };
        }();

        // Menus/Dropdowns Collapse
        var hideAutocomplete = function hideAutocomplete() {
          $('#navbar #lit-search-autocomplete-form').removeClass('active');
          $('#navbar .lit-search-autocomplete-results').removeClass('active');
        };

        $('#user-dropdown').on('show.bs.collapse', function () {
          $('.collapse').collapse('hide');
          $('#navbar .search-toggle').addClass('no-search');
          hideAutocomplete();
        });

        $('#navbar-collapse').on('show.bs.collapse', function () {
          $('.collapse').collapse('hide');
          $('#navbar .search-toggle').addClass('no-search');
          hideAutocomplete();
        });

        $('#navbar .search-toggle').on('click', function () {
          $(this).toggleClass('no-search');
          $('.main-container').toggleClass('no-scroll');
          $('.collapse').collapse('hide');
          $('#navbar #lit-search-autocomplete-form').toggleClass('active');
          $('#navbar .lit-search-autocomplete-results').toggleClass('active');
          $('#navbar .lit-search-autocomplete-form .lit-search-autocomplete-field').focus();
        });

        // DOM modifications
        // Equal Height
        $('.logo-bg-holder').matchHeight({
          target: $('.path-node .region-content > .system-block-wrap > article.full > .container')
        });
        $('.views-row .front .content').matchHeight();
        $('.views-row .teaser .content').matchHeight();

        var loginFormBlock = $('#block-litteratursiden-login');
        var loginFormTitle = $('#block-litteratursiden-login .block-title');
        loginFormBlock.find('ul li:first-child a').insertBefore(loginFormTitle);
        loginFormBlock.find('ul li:last-child a').insertBefore(loginFormTitle);
        $('#block-litteratursiden-login .create-account-link, #block-litteratursiden-login .facebook-login').wrapAll('<div class="flex-row"></div>');


        // Filters checkboxes
        $('.facets-checkbox').change(function () {
          if (!$(this).prop('checked')) {
            $(this).parent().removeClass('checked');
          }
          else {
            $(this).parent().addClass('checked');
          }
        });

        $('.filters-open-btn, .sidebar .close').on('click', function () {
          $('aside.sidebar').toggleClass('shown');
        });

        // Node pages
        if ($('article.full .book-review .review-author .image-col .field--type-image a').exists()) {
          var userLink = $('.book-review .review-author .image-col .field--type-image a').attr('href');
          $('.book-review .info-col .user-link').attr('href', userLink);
        }

        // Accordion
        function toggleIcon(e) {
          $(e.target).prev('.panel-heading').find(".more-less").toggleClass('glyphicon-plus glyphicon-minus');
        }

        $('.panel-group').on('hidden.bs.collapse', toggleIcon);
        $('.panel-group').on('shown.bs.collapse', toggleIcon);

        // Collapse comments at mobiles
        if ($(window).width() < 767) {
          $('.comments-body').addClass('collapse in');
          $('.comments-header').addClass('in');
          $('.comments-header').on('click', function () {
            $(this).toggleClass('in');
            $('.comments-body').collapse('toggle');
          });
        }

        // Wrap numbers at search page
        $(".path-search .content-area .search-page-container>.region-content>.form-group>div>header").html(function (_, html) {
          return html.replace(/(\d+)/, '<span>$1</span>');
        });

        // Remove HTML5 default validation
        $('form.node-form, form.contact-form').attr('novalidate', true);

        // Fix for IOS form zooming
        document.addEventListener('touchmove', function(event) {
          event = event.originalEvent || event;
          if (event.scale !== 1) {
            event.preventDefault();
          }
        }, false);

        // Submit login form on Enter
        $('.user-login-form').keypress(function(evt) {
          var keyCode = evt ? (evt.which ? evt.which : evt.keyCode) : event.keyCode;
          if (keyCode == 13) {
            $('.user-login-form').submit();
          }
        });

        // Wait for Facets load
        window.onload = function () {
          var searchCbxs = $(document).find('.facets-checkbox');
          searchCbxs.each(function (index, element) {
            if ($(element).prop('checked')) {
              $(element).parent().addClass('checked');
            }
          });
          var userLink = $('.field-group-accordion-wrapper .book-analysis article.additional-info .content-owner-portrait .field--name-field-user-picture > a').attr('href');
          $('.field-group-accordion-wrapper .book-analysis article.additional-info .content-owner-portrait .user-link').attr('href', userLink);
        };
      });
    }
  };
})(jQuery);
