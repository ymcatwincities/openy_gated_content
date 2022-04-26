(function($) {
  // Pass current page path to websocket, so we can separate chatrooms based on livestream node.
  var protocol = drupalSettings.openy_gc_livechat.mode == 'https' ? 'wss://' : 'ws://';
  var conn = new WebSocket(
    protocol + window.location.host + ':' + drupalSettings.openy_gc_livechat.port + window.location.pathname + window.location.hash.replace('#', '')
  );
  conn.onopen = function(e) {
    console.log('Connection established!');
  };

  conn.onmessage = function(e) {
    var data = JSON.parse(e.data);
    if (data.message_type == 'history') {
      for (var i in data.history) {
        $('.chat-messages').append('<p>' + data.history[i].username + ': ' + data.history[i].message + '</p>');
      }
    }
    else {
      $('.chat-messages').append('<p>' + data.from + ': ' + data.msg + '</p>');
    }
  };

  var $form = $('#chat-form');
  $('body').on('submit', $form, function(e) {
    e.preventDefault();
    var textarea = $('#edit-chat-message');
    var message = textarea.val();
    var data = {
      chatroom_id : window.location.pathname + window.location.hash.replace('#', ''),
      msg : message
    };
    conn.send(JSON.stringify(data));
    textarea.val('');
  });

})(jQuery);
