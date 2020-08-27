import client from '@/client';

export default {
  state: {
    config: null,
  },
  actions: {
    async daxkobarcodeAuthorize(context, data) {
      return client
        .get('session/token')
        .then((response) => client({
          url: context.getters.getDaxkoBarcodeConfig.barcodeValidate,
          method: 'post',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': response.data,
          },
          params: {
            _format: 'json',
          },
          data,
        })
          .then((response2) => {
            if (response2.status === 200) {
              // Call the base auth authorize action.
              context.dispatch('authorize', response2.data.user);
            }
            return response2;
          })
          .catch((error) => {
            throw error;
          }))
        .catch((error) => {
          throw error;
        });
    },
    daxkobarcodeLogout(context) {
      // Call the base auth logout action.
      context.dispatch('logout');
    },
    daxkobarcodeConfigure(context, config) {
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
    getDaxkoBarcodeReCaptchaKey: (state) => state.config.reCaptchaKey,
  },
};
