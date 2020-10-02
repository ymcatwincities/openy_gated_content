# openy\_gc\_log

In order to alter Virtual Y related user roles use next code 
to initialize Event Subscriber in "openy_gc_log.services.yml" 

`services:
  gated_content_events_user_login:
    class: '\Drupal\openy_gc_log\EventSubscriber\GCExampleUserLoginSubscriber'
    tags:
      - { name: 'event_subscriber' }`
