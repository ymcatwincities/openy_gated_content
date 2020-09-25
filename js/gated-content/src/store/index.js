import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import auth from './modules/auth';
import settings from './modules/settings';

Vue.use(Vuex);

const vuexLocalStorage = new VuexPersistence({
  key: 'vuex',
  storage: window.localStorage,
  reducer: (state) => ({
    auth: {
      user: state.auth.user,
      loggedIn: state.auth.loggedIn,
    },
  }),
});

export default new Vuex.Store({
  state: { },
  mutations: { },
  actions: { },
  modules: {
    settings,
    auth,
  },
  plugins: [vuexLocalStorage.plugin],
});
