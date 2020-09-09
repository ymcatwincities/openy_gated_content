import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import auth from './modules/auth';
import settings from './modules/settings';
import authCustom from './modules/auth/custom';
import authDaxkoBarcode from './modules/auth/daxkobarcode';
import authDaxkoSSO from './modules/auth/daxkosso';
import authPersonify from './modules/auth/personify';

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
    // @TODO: It seems like only one provider can use recaptcha at a time.
    // This breaks `authCustom` and should be resolved before PR is merged.
    authDaxkoBarcode,
    authCustom,
    authDaxkoSSO,
    authPersonify,
  },
  plugins: [vuexLocalStorage.plugin],
});
