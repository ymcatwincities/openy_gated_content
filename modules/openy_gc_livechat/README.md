# Livechat frature for Virtual Y livestreams

This module provides ability to add livechat for the LiveStream type of events of the Virtual Y.

Livechat feature require's running chat daemon on your server. For this purpose, we've created bash script that you
could use on your server.

You could easily install daemon script at your server by following this plan:

1. Create file `/etc/systemd/system/wssserver.service`
2. Put this content to your service daemon:
```
[Unit]
Description=WSS server service
After=mysqld.service
StartLimitIntervalSec=0
[Service]
Type=simple
Restart=always
RestartSec=1
User=root
ExecStart=/var/www/html/virtualy/vendor/bin/drush ev ‘\Drupal\openy_gc_livechat\GCSocketServer::run();’
[Install]
WantedBy=multi-user.target
```
3. Add this line to your crontab (to open crontab editor, please use this command: `crontab -e`):
```
0 */4 * * * systemctl restart wssserver.service
```

# How it works?

If you want to use this feature, you have to enable this module at your Virtual Y instance.
(Please, do not forget to configure background daemon before that).
Once you enable it, you could start using livechat at any Virtual Y Livestream page.

Your users could start using the chat once your virtual meeting is started.
Before that, chat feature will be marked as `disabled`.

# Disable chat

Every user that have `virtual_trainer` role can disable chat any time.
Disabling the chat automatically erases its history.
![Disable button for admin](/modules/openy_gc_livachat/images/vy_chat_disable.png "Livechat disable button")

# Chats history

Each Livestream saves its history for a certain amount of time (by default - 30 days).

All saved chats have admin interface, where you could use search and filter functionality
to get history of a certain Livestream or a group of the Livestreams.

URI for chat history overview page is: `/admin/virtual-y/chats`

