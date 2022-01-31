(function($) {
  // Pass current page path to websocket, so we can separate chatrooms based on livestream node.
  var conn = new WebSocket('ws://' + window.location.host + ':8081' + window.location.pathname + window.location.hash.replace('#', ''));
  conn.onopen = function(e) {
    console.log('Connection established!');
  };

  conn.onmessage = function(e) {
    var data = JSON.parse(e.data);
    $('.chat-messages').append('<p>' + data.from + ': ' + data.msg + '</p>');
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
