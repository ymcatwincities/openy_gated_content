export default {
  state: {
    data: null,
  },
  actions: {
    setSettings(context, payload) {
      context.commit('setSettings', payload);
    },
  },
  mutations: {
    setSettings(state, payload) {
      state.data = payload;
    },
  },
  getters: {
    getAppSettings: (state) => state.data,
  },
};
