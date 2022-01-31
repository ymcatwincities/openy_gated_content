export default {
  state: {
    liveChatSession: [],
    unreadLiveChatMessages: 0,
  },
  actions: {
    sendLiveChatMessage(context, message) {
      const chatRoomObj = {
        author: context.getters.liveChatLocalName,
        message,
        date: new Date(),
      };

      const msgObj = {
        chatroom_id: context.getters.liveChatMeetingId,
        username: context.getters.liveChatLocalName,
        user_id: window.drupalSettings.user.uid,
        message,
      };

      context.commit('addLiveChatMessage', chatRoomObj);
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
