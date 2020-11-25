import Vue from 'vue';
import Vuex from 'vuex';
import settings from './modules/settings';
import headline from './modules/headline';

Vue.use(Vuex);

export default new Vuex.Store({
  state: { },
  mutations: { },
  actions: { },
  modules: {
    settings,
    headline,
  },
});
