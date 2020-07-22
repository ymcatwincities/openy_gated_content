const Telemetry = {
  // eslint-disable-next-line no-unused-vars
  install(Vue, options) {
    // eslint-disable-next-line no-param-reassign
    Vue.prototype.$tm = {
      trackEventLoggedIn(user) {
        console.log('trackEventLoggedIn', user);
      },
      trackEventEntityView(entityType, entityBundle, entityId) {
        console.log('trackEventEntityView', entityType, entityBundle, entityId);
      },
    };
  },
};

export default Telemetry;
