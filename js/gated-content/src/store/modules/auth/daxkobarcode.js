import client from '@/client';

export default {
  state: {
    config: null,
  },
  actions: {
    async daxkobarcodeAuthorize(context, data) {
      console.log("Post to: ", context.getters.getDaxkoBarcodeConfig.barcodeValidate);

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
          data: {
            recaptchaToken: data.recaptchaToken,
            barcode: data.barcode
          }
        })
          .then((response2) => {
            console.log(response2.data);
            if (response2.status === 200) {
              // Call the base auth authorize action.
              context.dispatch('authorize', response2.data.user);
            }
            return response2;
          })
          .catch((error) => {
            console.log(error.response.data.message);
            throw error;
          }))
        .catch(error => {
          throw error;
        });
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
