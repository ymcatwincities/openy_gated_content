import $ from 'jquery';
import Drupal from 'drupal';
import client from './client';

Drupal.behaviors.openy_gc_log_subscribe = {
  attach(context) {
    document.body.addEventListener('virtual-y-log', (event) => {
      client
        .post('virtual-y/log', event.detail)
        .catch((error) => {
        });
    });
  },
};
