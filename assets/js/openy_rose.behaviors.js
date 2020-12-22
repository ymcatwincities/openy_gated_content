/**
 * Drupal behaviors for openy_rose
 */
(function ($) {
  "use strict";

  /**
   * Set css class 'primary-menu-minimize' for body element after primary menu affix activated on scroll
   * This function is needed for Virtual Y top menu to follow primary menu height change
   */
  Drupal.behaviors.OpenyRoseAffixClassForPrimaryMenu = {
    attach: function (context, settings) {
      var originalAddClassMethod    = jQuery.fn.addClass;
      var originalRemoveClassMethod = jQuery.fn.removeClass;
      jQuery.fn.addClass            = function () {
        var result = originalAddClassMethod.apply(this, arguments);
        jQuery(this).trigger('classChanged');
        return result;
      }
      jQuery.fn.removeClass         = function () {
        var result = originalRemoveClassMethod.apply(this, arguments);
        jQuery(this).trigger('classChanged');
        return result;
      }

      $(".top-navs.hidden-xs").on(
        "classChanged", function () {
          if ($('.top-navs.hidden-xs').hasClass('affix')
            && !$('body').hasClass('primary-menu-minimize')) {
            $('body').addClass('primary-menu-minimize');
          } else if ($('body').hasClass('primary-menu-minimize')) {
            $('body').removeClass('primary-menu-minimize');
          }
        });
    }
  };
})(jQuery);
