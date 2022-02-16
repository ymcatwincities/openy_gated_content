export default {
  state: {
    liveChatSession: [],
    unreadLiveChatMessages: 0,
    bottomScrollOn: true,
    onlineClientCount: 0,
  },
  actions: {
    sendLiveChatMessage(context, message) {
      const msgObj = {
        chatroom_id: context.getters.liveChatMeetingId,
        title: context.getters.liveChatMeetingTitle,
        start: context.getters.liveChatMeetingStart,
        username: context.getters.liveChatLocalName,
        uid: window.drupalSettings.user.uid,
        message,
      };

      context.dispatch('sendLiveChatData', msgObj);
    },
    sendLiveChatTechMessage(context, message) {
      message.chatroom_id = context.getters.liveChatMeetingId;
      message.uid = window.drupalSettings.user.uid;

      context.dispatch('sendLiveChatData', message);
    },
    async receiveChatMessage(context, msgObj) {
      context.commit('addLiveChatMessage', msgObj);
      if (!context.getters.isShowLiveChatModal) {
        // For closed chat modal increment unread messages.
        context.commit('incUnreadLiveChatMessages');
      }
    },
    updateBottomScrollOn(context, scrollStatus) {
      context.commit('setBottomScrollOn', scrollStatus);
    },
    updateOnlineClientCount(context, count) {
      context.commit('setOnlineClientCount', count);
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
    setBottomScrollOn(state, value) {
      state.bottomScrollOn = value;
    },
    setOnlineClientCount(state, value) {
      state.onlineClientCount = value;
    },
  },
  getters: {
    liveChatSession: (state) => state.liveChatSession,
    unreadLiveChatMessagesCount: (state) => state.unreadLiveChatMessages,
    bottomScrollOn: (state) => state.bottomScrollOn,
    onlineClientCount: (state) => state.onlineClientCount,
  },
};
