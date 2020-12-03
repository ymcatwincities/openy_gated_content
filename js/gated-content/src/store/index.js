import Vue from 'vue';
import Vuex from 'vuex';
import settings from './modules/settings';
import favorites from './modules/favorites';

Vue.use(Vuex);

export default new Vuex.Store({
  state: { },
  mutations: { },
  actions: { },
  modules: {
    settings,
    favorites,
  },
});
