export default {
  state: {
    audioContext: null,
    instructorMediaStream: null,
    customerMediaStream: null,
    partnerCamEnabled: false,
    partnerMicEnabled: false,
  },
  actions: {
    async initMediaStream(context) {
      const constraints = {
        audio: {
          echoCancellation: true,
          noiseSuppression: true,
          autoGainControl: false,
          deviceId: context.getters.chosenAudioInputDeviceId,
        },
        video: {
          width: { min: 640, ideal: 1920, max: 1920 },
          height: { min: 400, ideal: 1080 },
          aspectRatio: 1.777777778,
          deviceId: context.getters.chosenVideoInputDeviceId,
        },
      };

      await navigator.mediaDevices.getUserMedia(constraints)
        .then((mediaStream) => context.dispatch('setLocalMediaStream', mediaStream))
        .catch((error) => {
          context.dispatch('debugLog', ['Init local stream error:', error]);
        });
    },
    setPartnerMediaStream(context, value) {
      if (context.getters.instructorRole) {
        context.commit('setCustomerMediaStream', value);
      } else {
        context.commit('setInstructorMediaStream', value);
      }
    },
    async setLocalMediaStream(context, mediaStream) {
      if (context.getters.instructorRole) {
        context.commit('setInstructorMediaStream', mediaStream);
      } else {
        context.commit('setCustomerMediaStream', mediaStream);
      }
    },
    async closeLocalMediaStream(context) {
      if (context.getters.localMediaStream !== null) {
        context.getters.localMediaStream.getTracks().forEach((track) => {
          track.stop();
        });
        context.dispatch('setLocalMediaStream', null);
      }
    },
    async closeRemoteMediaStream(context) {
      context.dispatch('setPartnerMediaStream', null);
      context.dispatch('setPartnerCamEnabled', false);
      context.dispatch('setPartnerMicEnabled', false);
    },
    async setPartnerCamEnabled(context, value) {
      context.commit('setPartnerCamEnabled', value);
    },
    async setPartnerMicEnabled(context, value) {
      context.commit('setPartnerMicEnabled', value);
    },
  },
  mutations: {
    setInstructorMediaStream(state, value) {
      state.instructorMediaStream = value;
    },
    setCustomerMediaStream(state, value) {
      state.customerMediaStream = value;
    },
    setPartnerCamEnabled(state, value) {
      state.partnerCamEnabled = value;
    },
    setPartnerMicEnabled(state, value) {
      state.partnerMicEnabled = value;
    },
  },
  getters: {
    localMediaStream: (state) => (state.instructorRole
      ? state.instructorMediaStream
      : state.customerMediaStream),
    partnerMediaStream: (state) => (state.instructorRole
      ? state.customerMediaStream
      : state.instructorMediaStream),
    partnerCamEnabled: (state) => state.partnerCamEnabled,
    partnerMicEnabled: (state) => state.partnerMicEnabled,
    audioContext: (state) => {
      if (state.audioContext === null) {
        var AudioContext = window.AudioContext || window.webkitAudioContext;
        state.audioContext = new AudioContext();
      }
      return state.audioContext;
    },
  },
};
