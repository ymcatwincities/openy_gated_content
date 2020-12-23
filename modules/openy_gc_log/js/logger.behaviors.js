(function ($, Drupal) {
 Drupal.behaviors.openy_gc_log_subscribe = {
   attach: function (context) {
     // Only run this script on full documents, not ajax requests.
     if (context !== document) {
       return;
     }
     document.body.addEventListener('virtual-y-log', function (event) {
       $.ajax({
         url: "/virtual-y/log",
         method: "POST",
         contentType: "application/json",
         data: JSON.stringify(event.detail),
         processData: false,
       });
     });
   }
 }
})(jQuery, Drupal);
