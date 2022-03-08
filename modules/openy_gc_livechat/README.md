# Livechat Feature for Virtual Y

This module provides the ability to add livechat for Livestream events in Virtual Y.

Livechat features require running a chat daemon on your server. To install the chat daemon on your server:

1. Create a service file in your system directory, for instance: `/etc/systemd/system/wssserver.service`
2. Add this configuration to the file:

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

3. Add this line to your crontab (to open your crontab editor use: `crontab -e`):
```
0 */4 * * * systemctl restart wssserver.service
```

# How it works

To use this feature, enable `openy_gc_livechat` on your Virtual Y site. (Please, do not forget to configure the background daemon before that). Once you enable it, you can start using livechat on any Virtual Y Livestream page.

Your users can start using the chat once your virtual meeting is started. Before that, chat features will be marked as `disabled`.

Users that have the `virtual_trainer` role can disable chat any time on the Livestream node edit page. Disabling the chat automatically erases its history.

# Chats history

Each Livestream saves its history for a certain amount of time (by default - 30 days).

All saved chats have an admin interface where you can search and filters to get a history of a certain Livestream or a group of Livestreams.

The chat history view can be viewed at `/admin/virtual-y/chats`.
