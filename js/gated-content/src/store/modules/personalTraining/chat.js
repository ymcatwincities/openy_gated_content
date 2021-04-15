export default {
  state: {
    chatSession: [],
  },
  actions: {
    sendChatMessage(context, message) {
      const msgObj = {
        author: context.getters.localName,
        message,
        date: new Date(),
      };
      context.commit('addChatMessage', msgObj);
      if (context.state.peerDataConnection) {
        context.state.peerDataConnection.send(msgObj);
      }
    },
    async receiveChatMessage(context, msgObj) {
      context.commit('addChatMessage', msgObj);
    },
  },
  mutations: {
    addChatMessage(state, message) {
      state.chatSession.push(message);
    },
  },
  getters: {
    chatSession: (state) => state.chatSession,
  },
};
