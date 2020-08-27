import client from '@/client';

export default {
  state: {
    config: null,
  },
  actions: {
    async customAuthorize(context, data) {
      return client
        .get('session/token')
        .then((response) => client({
          url: context.getters.getCustomConfig.apiEndpoint,
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
            email: data.email,
            path: data.path,
          },
        })
          .then((response2) => {
            if (response2.status === 200) {
              // Call the base auth authorize action.
              context.dispatch('authorize', response2.data.user);
            }
            return response2;
          })
          .catch((error) => {
            console.error(error);
            throw error;
          }))
        .catch((error) => {
          throw error;
        });
    },
    async customEmailConfirmation(context, data) {
      return client
        .get('session/token')
        .then((response) => client({
          url: context.getters.getCustomConfig.emailVerificationApiEndpoint,
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
            // Call the base auth authorize action.
            context.dispatch('authorize', response2.data.user);
            return response2;
          })
          .catch((error) => {
            console.error(error);
            throw error;
          }))
        .catch((error) => {
          throw error;
        });
    },
    customLogout(context) {
      context.dispatch('logout');
    },
    customConfigure(context, config) {
      context.commit('setCustomConfig', config);
    },
  },
  mutations: {
    setCustomConfig(state, config) {
      state.config = config;
    },
  },
  getters: {
    getCustomConfig: (state) => state.config,
    getCustomReCaptchaKey: (state) => state.config.reCaptchaKey,
  },
};
