import Vue from 'vue';
import Vuex from 'vuex';
import settings from './modules/settings';
import headline from './modules/headline';
import favorites from './modules/favorites';
import categories from './modules/categories';
import personalTraining from './modules/personalTraining';
import liveChat from './modules/liveChat';
import debugLog from './modules/debugLog';

Vue.use(Vuex);

export default new Vuex.Store({
  state: { },
  mutations: { },
  actions: { },
  modules: {
    settings,
    headline,
    favorites,
    categories,
    personalTraining,
    liveChat,
    debugLog,
  },
});
