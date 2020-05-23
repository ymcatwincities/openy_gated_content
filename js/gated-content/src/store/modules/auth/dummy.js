export default {
  state: {
    config: null,
  },
  actions: {
    dummyAuthorize(context) {
      console.log('You are authorized now!');
      // Call the base auth authorize action.
      context.dispatch('authorize', {});
    },
    dummyLogout(context) {
      console.log('Logging you out');
      // Call the base auth logout action.
      context.dispatch('logout');
    },
    dummyConfigure(context, config) {
      console.log('Setting configuration', config);
      context.commit('setDummyConfig', config);
    },
  },
  mutations: {
    setDummyConfig(state, config) {
      state.config = config;
    },
  },
  getters: {
    getDummyConfig: (state) => state.config,
  },
};
