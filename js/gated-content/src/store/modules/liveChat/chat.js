export default {
  state: {
    liveChatSession: [],
    unreadLiveChatMessages: 0,
  },
  actions: {
    sendLiveChatMessage(context, message) {
      const msgObj = {
        chatroom_id: context.getters.liveChatMeetingId,
        username: context.getters.liveChatLocalName,
        uid: window.drupalSettings.user.uid,
        message,
      };

      context.dispatch('sendLiveChatData', msgObj);
    },
    async receiveChatMessage(context, msgObj) {
      context.commit('addLiveChatMessage', msgObj);
      if (!context.getters.isShowLiveChatModal) {
        // For closed chat modal increment unread messages.
        context.commit('incUnreadLiveChatMessages');
      }
    },
  },
  mutations: {
    addLiveChatMessage(state, message) {
      state.liveChatSession.push(message);
    },
    incUnreadLiveChatMessages(state) {
      state.unreadLiveChatMessages += 1;
    },
    resetUnreadLiveChatMessages(state) {
      state.unreadLiveChatMessages = 0;
    },
  },
  getters: {
    liveChatSession: (state) => state.liveChatSession,
    unreadLiveChatMessagesCount: (state) => state.unreadLiveChatMessages,
  },
};
