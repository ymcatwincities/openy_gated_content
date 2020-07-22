import Vue from 'vue';

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
      Vue.prototype.$tm.trackEventLoggedIn(user);
    },
    logout(context) {
      context.commit('unsetUser');
    },
    setAuthPlugin(context, plugin) {
      context.commit('setAuthPlugin', plugin);
    },
    setAppUrl(context, appUrl) {
      context.commit('setAppUrl', appUrl);
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
    setAppUrl(state, appUrl) {
      state.appUrl = appUrl;
    },
  },
  getters: {
    isLoggedIn: (state) => state.loggedIn,
    authPlugin: (state) => state.authPlugin,
    getAppUrl: (state) => state.appUrl,
  },
};
