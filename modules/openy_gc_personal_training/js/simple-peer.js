/**
 * Drupal behaviors for openy_rose
 */
(function ($) {
  "use strict";

  /**
   * Set css class 'primary-menu-minimize' for body element after primary menu affix activated on scroll
   * This function is needed for Virtual Y top menu to follow primary menu height change
   */
  Drupal.behaviors.VirtualYPT = {
    attach: function (context, settings) {
      var Peer = require('simple-peer');

      // get video/voice stream
      navigator.mediaDevices.getUserMedia({
        video: true,
        audio: true
      }).then(gotMedia).catch(() => {})

      function gotMedia (stream) {
        var peer1 = new Peer({ initiator: true, stream: stream })
        var peer2 = new Peer()

        peer1.on('signal', data => {
          peer2.signal(data)
        })

        peer2.on('signal', data => {
          peer1.signal(data)
        })

        peer2.on('stream', stream => {
          // got remote video stream, now let's show it in a video tag
          var video = document.querySelector('video')

          if ('srcObject' in video) {
            video.srcObject = stream
          } else {
            video.src = window.URL.createObjectURL(stream) // for older browsers
          }

          video.play()
        })
    }
  };
})(jQuery);