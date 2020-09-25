import store from '@/store';

const Log = {
  // eslint-disable-next-line no-unused-vars
  install(Vue, options) {
    // eslint-disable-next-line no-param-reassign
    Vue.prototype.$log = {
      trackEventEntityView(entityType, entityBundle, entityId) {
        const user = store.getters.getUser;
        const event = new CustomEvent('virtual-y-log', {
          detail: {
            uid: window.drupalSettings.user.uid !== undefined ? window.drupalSettings.user.uid : '',
            event_type: 'entityView',
            entity_type: entityType,
            entity_bundle: entityBundle,
            entity_id: entityId,
          },
        });
        document.body.dispatchEvent(event);
      },
    };
  },
};

export default Log;
