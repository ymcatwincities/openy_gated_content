export default {
  state: {
    micEnabled: true,
    cameraEnabled: true,
    view: 'view-inset',
    fullScreenMode: false,
  },
  actions: {
    setHorizontalView(context) {
      context.commit('showViewOptionsModal', false);
      context.commit('setView', 'view-horizontal');
    },
    setVerticalView(context) {
      context.commit('showViewOptionsModal', false);
      context.commit('setView', 'view-vertical');
    },
    setInsetView(context) {
      context.commit('showViewOptionsModal', false);
      context.commit('setView', 'view-inset');
    },
    toggleMicEnabled(context) {
      context.commit('setMicEnabled', !context.getters.isMicEnabled);
      context.dispatch('sendLocalMicEnabledState', context.getters.isMicEnabled);
      if (context.getters.localMediaStream) {
        context.getters.localMediaStream.getAudioTracks().forEach((t) => {
          // eslint-disable-next-line no-param-reassign
          t.enabled = context.getters.isMicEnabled;
        });
      }
    },
    toggleCameraEnabled(context) {
      context.commit('setCameraEnabled', !context.getters.isCameraEnabled);
      context.dispatch('sendLocalCamEnabledState', context.getters.isCameraEnabled);
      if (context.getters.localMediaStream) {
        context.getters.localMediaStream.getVideoTracks().forEach((t) => {
          // eslint-disable-next-line no-param-reassign
          t.enabled = context.getters.isCameraEnabled === true;
        });
      }
    },
    toggleFullScreenMode(context) {
      if (document.fullscreenElement) {
        document.exitFullscreen()
          .then(() => context.commit('setFullScreenMode', false))
          .catch((err) => console.error(err));
      } else {
        const elem = document.querySelector('.personal-training-full-screen');
        elem.requestFullscreen()
          .then(() => context.commit('setFullScreenMode', true))
          .catch((err) => console.error(err));
      }
    },
  },
  mutations: {
    setMicEnabled(state, value) {
      state.micEnabled = value;
    },
    setCameraEnabled(state, value) {
      state.cameraEnabled = value;
    },
    setView(state, value) {
      state.view = value;
    },
    setFullScreenMode(state, value) {
      state.fullScreenMode = value;
    },
  },
  getters: {
    view: (state) => state.view,
    isMicEnabled: (state) => state.micEnabled,
    isCameraEnabled: (state) => state.cameraEnabled,
    isFullScreen: (state) => state.fullScreenMode,
  },
};
