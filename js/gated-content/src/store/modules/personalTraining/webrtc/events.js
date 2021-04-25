export default {
  state: {
    remoteVideoState: false,
  },
  actions: {
    async callEndedEvent(context) {
      context.dispatch('closeMediaConnection');
    },
    async sendCallEndedEvent(context) {
      context.dispatch('sendData', 'callEndedEvent');
    },
    async setRemoteVideoStateEvent(context, value) {
      context.commit('setRemoteVideoState', value);
    },
    async sendVideoStateEvent(context, value) {
      context.dispatch('sendData', { videoStateEvent: value });
    },
  },
  mutations: {
    setRemoteVideoState(state, value) {
      state.remoteVideoState = value;
    },
  },
  getters: {
    remoteVideoState: (state) => state.remoteVideoState,
  },
};
