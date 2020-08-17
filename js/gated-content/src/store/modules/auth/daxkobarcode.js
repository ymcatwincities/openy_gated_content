import client from '@/client';

export default {
  state: {
    config: null,
  },
  actions: {
    daxkobarcodeAuthorize(context) {
      console.log('You are authorized now!');
      // Call the base auth authorize action.
      context.dispatch('authorize', {});
    },
    daxkobarcodeLogout(context) {
      console.log('Logging you out');
      // Call the base auth logout action.
      context.dispatch('logout');
    },
    daxkobarcodeConfigure(context, config) {
      console.log('Setting configuration', config);
      context.commit('setDaxkoBarcodeConfig', config);
    },
  },
  mutations: {
    setDaxkoBarcodeConfig(state, config) {
      state.config = config;
    },
  },
  getters: {
    getDaxkoBarcodeConfig: (state) => state.config,
    getReCaptchaKey: (state) => state.config.reCaptchaKey,
  },
};
