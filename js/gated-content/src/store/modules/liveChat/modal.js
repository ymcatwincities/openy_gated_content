export default {
  state: {
    showLiveChatUserNameModal: false,
    showLiveChatModal: false,
    openLiveChatNameModal: false,
    openLiveChatConfigNameModal: false,
  },
  actions: {
    toggleShowLiveChatModal(context) {
      context.commit('showLiveChatModal', !context.state.showLiveChatModal);
      if (context.state.showLiveChatModal) {
        // See chat.js
        context.commit('resetUnreadLiveChatMessages');
      }
    },
    toggleShowLiveChatUserNameModal(context, modal) {
      if (!context.state.openLiveChatNameModal) {
        context.commit('showLiveChatUserNameModal', !context.state.showLiveChatUserNameModal);
        if (modal) {
          context.commit('updateOpenLiveChatNameModal', true);
        }
      }
    },
    toggleShowLiveChatConfigNameModal(context, modal) {
      if (modal) {
        context.commit('updateOpenLiveChatNameModal', !context.state.openLiveChatNameModal);
      }
      context.commit('showLiveChatUserNameModal', !context.state.showLiveChatUserNameModal);
      context.commit('updateOpenLiveChatConfigNameModal', !context.state.openLiveChatConfigNameModal);
    },
  },
  mutations: {
    showLiveChatUserNameModal(state, value) {
      state.showLiveChatUserNameModal = value;
    },
    showLiveChatModal(state, value) {
      state.showLiveChatModal = value;
    },
    updateOpenLiveChatNameModal(state, value) {
      state.openLiveChatNameModal = value;
    },
    updateOpenLiveChatConfigNameModal(state, value) {
      state.openLiveChatConfigNameModal = value;
    },
  },
  getters: {
    isShowLiveChatUserNameModal: (state) => state.showLiveChatUserNameModal,
    isShowLiveChatModal: (state) => state.showLiveChatModal,
    isOpenLiveChatNameModal: (state) => state.openLiveChatNameModal,
    isOpenLiveChatConfigNameModal: (state) => state.openLiveChatConfigNameModal,
  },
};
