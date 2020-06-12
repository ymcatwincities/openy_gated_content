import client from '@/client';

export default {
  state: {
    config: null,
  },
  actions: {
    async personifyAuthorize(context) {
      return client.post(context.getters.getPersonifyConfig.api_login_check)
        .then((r) => {
          // Check user data.
          if (r.data.user && r.data.user.email) {
            context.dispatch('authorize', r.data.user);
          } else {
            // Redirect to Personify login page.
            window.location = context.getters.getPersonifyConfig.api_login_personify;
          }
        })
        .catch((error) => {
          console.error(error);
          throw error;
        });
    },
    personifyLogout(context) {
      client({
        url: context.getters.getPersonifyConfig.api_logout,
        method: 'post',
      })
        .then((response) => {
          context.dispatch('logout');
          window.location = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    personifyConfigure(context, config) {
      context.commit('setPersonifyConfig', config);
    },
  },
  mutations: {
    setPersonifyConfig(state, config) {
      state.config = config;
    },
  },
  getters: {
    getPersonifyConfig: (state) => state.config,
  },
};
