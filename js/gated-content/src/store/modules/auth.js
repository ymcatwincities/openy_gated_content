export default {
  state: {
    authPlugin: 'dummy',
    id: null,
    name: null,
    user: {},
    loggedIn: false,
    appUrl: '',
  },
  actions: {
    authorize(context, user) {
      context.commit('setUser', user);
      if (context.state.appUrl.length > 0) {
        window.location = context.state.appUrl;
      }
    },
    logout(context) {
      context.commit('unsetUser');
    },
    setAuthPlugin(context, plugin) {
      context.commit('setAuthPlugin', plugin);
    },
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
      state.loggedIn = true;
    },
    unsetUser(state) {
      state.user = {};
      state.loggedIn = false;
    },
    setAuthPlugin(state, plugin) {
      state.authPlugin = plugin;
    },
  },
  getters: {
    isLoggedIn: (state) => state.loggedIn,
    authPlugin: (state) => state.authPlugin,
  },
};
