export default {
  state: {
    showLiveChatUserNameModal: false,
    showLiveChatModal: false,
  },
  actions: {
    toggleShowLiveChatModal(context) {
      context.commit('showLiveChatModal', !context.state.showLiveChatModal);
      if (context.state.showLiveChatModal) {
        // See chat.js
        context.commit('resetUnreadLiveChatMessages');
      }
    },
    toggleShowLiveChatUserNameModal(context) {
      context.commit('showLiveChatUserNameModal', !context.state.showLiveChatUserNameModal);
    },
  },
  mutations: {
    showLiveChatUserNameModal(state, value) {
      state.showLiveChatUserNameModal = value;
    },
    showLiveChatModal(state, value) {
      state.showLiveChatModal = value;
    },
  },
  getters: {
    isShowLiveChatUserNameModal: (state) => state.showLiveChatUserNameModal,
    isShowLiveChatModal: (state) => state.showLiveChatModal,
  },
};
