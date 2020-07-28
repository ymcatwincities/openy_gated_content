(function ($, Drupal) {
 Drupal.behaviors.openy_gc_log_subscribe = {
   attach: function (context) {
     document.body.addEventListener('virtual-y-log', (event) => {
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
