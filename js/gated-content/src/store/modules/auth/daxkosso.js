import client from '@/client';

export default {
  state: {
    config: null,
    loading: false,
  },
  actions: {
    async daxkossoAuthorize(context) {
      if (!context.getters.isLoggedIn) {
        const urlParams = new URLSearchParams(window.location.search);
        const state = urlParams.get('state');
        const code = urlParams.get('code');

        if ((state !== null) && (code !== null)) {
          const apiCheckEndPoint = `${context.getters.getDaxkoSSOConfig.check_url}?state=${state}&code=${code}`;
          await client.get(apiCheckEndPoint).then((result) => {
            if (result.data.error === 0) {
              context.dispatch('authorize', result.data.user);
            }
          });
          context.commit('setLoading', false);
        } else {
          // Redirect user if session is not started and there are no token in get.
          window.location = context.getters.getDaxkoSSOConfig.login_url;
        }
      }
    },
    daxkossoLogout(context) {
      window.history.replaceState({}, document.title, window.location.href.split('?')[0]);
      context.dispatch('logout');
    },
    daxkossoConfigure(context, config) {
      context.commit('setDaxkoSSOConfig', config);
    },
  },
  mutations: {
    setDaxkoSSOConfig(state, config) {
      state.config = config;
    },
    setLoading(state, value) {
      state.loading = value;
    },
  },
  getters: {
    getDaxkoSSOConfig: (state) => state.config,
  },
};
