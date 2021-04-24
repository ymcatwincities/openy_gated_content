export default {
  state: {
    chatSession: [],
    unreadMessages: 0,
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
      if (!context.getters.isShowChatModal) {
        // For closed chat modal increment unread messages.
        context.commit('incUnreadMessages');
      }
    },
  },
  mutations: {
    addChatMessage(state, message) {
      state.chatSession.push(message);
    },
    incUnreadMessages(state) {
      state.unreadMessages += 1;
    },
    resetUnreadMessages(state) {
      state.unreadMessages = 0;
    },
  },
  getters: {
    chatSession: (state) => state.chatSession,
    unreadMessagesCount: (state) => state.unreadMessages,
  },
};
