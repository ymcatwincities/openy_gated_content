export default {
  state: {
    ratchetServer: null,
    ratchetServerConnected: false,
  },
  actions: {
    async initRatchetServer(context) {
      const serverURL = `${window.location.host}:8081${window.location.hash.replace('#', '')}`;

      const ws = new WebSocket(`wss://${serverURL}`);

      ws.onopen = () => {
        context.commit('setRatchetServerConnected', true);
        console.log(context.getters.ratchetServerConnected);
      };

      ws.onmessage = (event) => {
        context.dispatch('receiveChatMessage', event.data);
        console.log(event.data);
      };

      ws.onclose = () => {
        context.commit('setRatchetServerConnected', false);
        _.delay(() => {
          context.dispatch('initRatchetServer');
        }, 1000);
      };

      context.commit('setRatchetServer', ws);
    },
    async sendLiveChatData(context, message) {
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
    ratchetServerConnected: (state) => state.ratchetServerConnected,
  },
};
