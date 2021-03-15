(function ($, Drupal) {
 Drupal.behaviors.openy_gc_auth_store_hash = {
   attach: function (context) {
     // Only run this script on full documents, not ajax requests.
     if (context !== document) {
       return;
     }
     if (!window.location.hash || window.location.hash === '#') {
       return;
     }
     $.cookie('openy_gc_auth_destination', window.location.hash);
   }
 }
})(jQuery, Drupal);
