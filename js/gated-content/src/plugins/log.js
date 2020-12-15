const Log = {
  // eslint-disable-next-line no-unused-vars
  install(Vue, options) {
    // eslint-disable-next-line no-param-reassign
    Vue.prototype.$log = {
      trackEvent(eventType, entityType, entityBundle, entityId) {
        const event = new CustomEvent('virtual-y-log', {
          detail: {
            uid: window.drupalSettings.user.uid !== undefined ? window.drupalSettings.user.uid : '',
            event_type: eventType,
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
