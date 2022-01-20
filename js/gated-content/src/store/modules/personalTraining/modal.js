export default {
  state: {
    userNameModal: false,
    showJoinOptionsModal: false,
    showViewOptionsModal: false,
    showChatModal: false,
    showLeaveMeetingModal: false,
    showDeviceManagerModal: false,
  },
  actions: {
    toggleShowChatModal(context) {
      context.commit('showChatModal', !context.state.showChatModal);
      if (context.state.showChatModal) {
        // See chat.js
        context.commit('resetUnreadMessages');
      }
    },
    async toggleShowJoinOptionsModal(context) {
      context.commit('showJoinOptionsModal', !context.state.showJoinOptionsModal);

      if (context.state.showJoinOptionsModal) {
        context.dispatch('initMediaStream')
          .then(() => context.dispatch('loadAndParseDeviceInfo'));
      } else {
        context.dispatch('closeLocalMediaStream');
      }
    },
    toggleShowUserNameModal(context) {
      context.commit('showUserNameModal', !context.state.userNameModal);
    },
    toggleShowLeaveMeetingModal(context) {
      context.commit('showLeaveMeetingModal', !context.state.showLeaveMeetingModal);
    },
    toggleViewOptionsModal(context) {
      context.commit('showViewOptionsModal', !context.state.showViewOptionsModal);
    },
    toggleDeviceManagerModal(context) {
      context.commit('showDeviceManagerModal', !context.state.showDeviceManagerModal);
    },
  },
  mutations: {
    showUserNameModal(state, value) {
      state.userNameModal = value;
    },
    showChatModal(state, value) {
      state.showChatModal = value;
    },
    showJoinOptionsModal(state, value) {
      state.showJoinOptionsModal = value;
    },
    showLeaveMeetingModal(state, value) {
      state.showLeaveMeetingModal = value;
    },
    showViewOptionsModal(state, value) {
      state.showViewOptionsModal = value;
    },
    showDeviceManagerModal(state, value) {
      state.showDeviceManagerModal = value;
    },
  },
  getters: {
    isShowUserNameModal: (state) => state.userNameModal,
    isShowJoinOptionsModal: (state) => state.showJoinOptionsModal,
    isShowLeaveMeetingModal: (state) => state.showLeaveMeetingModal,
    isShowViewOptionsModal: (state) => state.showViewOptionsModal,
    isShowChatModal: (state) => state.showChatModal,
    isShowDeviceManagerModal: (state) => state.showDeviceManagerModal,
  },
};
