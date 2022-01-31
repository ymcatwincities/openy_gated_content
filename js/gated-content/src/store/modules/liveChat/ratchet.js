export default {
  state: {
    ratchetServer: null,
    ratchetServerConnected: false,
  },
  actions: {
    async initRatchetServer(context) {

      const serverURL = `${window.location.host}:8081`;
      const { liveChatMeetingId } = context.getters;
      const ws = new WebSocket(`ws://${serverURL}/${liveChatMeetingId}`);

      console.log(ws);

      ws.addEventListener('onopen', () => {
        context.commit('setRatchetServerConnected', true);
        console.log(ws);
      });

      ws.addEventListener('onmessage', () => {
        context.commit('livestreamChatMessage', true);
        console.log(ws);
      });

      ws.addEventListener('close', () => {
        context.commit('setRatchetServerConnected', false);
        _.delay(() => {
          context.dispatch('initRatchetServer');
        }, 1000);
      });

      context.commit('setRatchetServer', ws);
    },
    async sendRatchetData(context, message) {
      if (context.getters.ratchetServerConnected) {
        context.getters.ratchetServer.send(JSON.stringify(message));
      }
    },
  },
  mutations: {
    setRatchetServer(state, value) {
      state.ratchetServer = value;
    },
    setRatchetServerConnected(state, value) {
      state.ratchetServerConnected = value;
    },
  },
  getters: {
    ratchetServer: (state) => state.ratchetServer,
  },
};
