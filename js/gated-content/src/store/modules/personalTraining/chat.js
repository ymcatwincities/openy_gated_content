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
      context.dispatch('sendData', { newMessage: msgObj });
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
