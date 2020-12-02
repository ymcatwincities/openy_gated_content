/**
 * @file
 * JavaScript for openy_gc_autologout.
 */
(function ($, Drupal) {

  var awayCallback = function() {
    $.ajax({
      url: drupalSettings.path.baseUrl + "openy_gc_autologout",
      type: "POST",
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-Requested-With', {
          toString: function () {
            return '';
          }
        });
      },
      success: function () {
        window.location = drupalSettings.openy_gc_autologout.redirect_url;
      },
      error: function (XMLHttpRequest, textStatus) {
        if (XMLHttpRequest.status === 403 || XMLHttpRequest.status === 404) {
          window.location = drupalSettings.openy_gc_autologout.redirect_url;
        }
      }
    });
  };

  var idle = new Idle({
    onAway: awayCallback,
    awayTimeout: typeof drupalSettings.openy_gc_autologout.autologout_timeout !== undefined ? drupalSettings.openy_gc_autologout.autologout_timeout * 1000 : 7200 * 1000,
  }).start();

})(jQuery, Drupal);
