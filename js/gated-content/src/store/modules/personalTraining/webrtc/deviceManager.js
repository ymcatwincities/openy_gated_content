export default {
  state: {
    videoInputDevices: [],
    audioInputDevices: [],
    audioOutputDevices: [],
    chosenVideoInputDeviceId: undefined,
    chosenAudioInputDeviceId: undefined,
    chosenAudioOutputDeviceId: undefined,
  },
  actions: {
    async loadAndParseDeviceInfo(context) {
      context.commit('clearDevices');
      const deviceInfos = await navigator.mediaDevices.enumerateDevices();

      // eslint-disable-next-line no-plusplus
      for (let i = 0; i !== deviceInfos.length; ++i) {
        const deviceInfo = deviceInfos[i];
        console.log(deviceInfo);
        if (deviceInfo.kind === 'audioinput') {
          const label = deviceInfo.label || `microphone ${context.getters.audioInputDevices.length + 1}`;
          if (!context.getters.chosenAudioInputDeviceId) {
            context.commit('setChosenAudioInputDeviceId', deviceInfo.deviceId);
          }
          context.commit('addAudioInputDevice', { deviceId: deviceInfo.deviceId, label });
        } else if (deviceInfo.kind === 'audiooutput') {
          const label = deviceInfo.label || `speaker ${context.getters.audioOutputDevices.length + 1}`;
          if (!context.getters.chosenAudioOutputDeviceId) {
            context.commit('setChosenAudioOutputDeviceId', deviceInfo.deviceId);
          }
          context.commit('addAudioOutputDevice', { deviceId: deviceInfo.deviceId, label });
        } else if (deviceInfo.kind === 'videoinput') {
          const label = deviceInfo.label || `camera ${context.getters.videoInputDevices.length + 1}`;
          if (!context.getters.chosenVideoInputDeviceId) {
            context.commit('setChosenVideoInputDeviceId', deviceInfo.deviceId);
          }
          context.commit('addVideoInputDevice', { deviceId: deviceInfo.deviceId, label });
        } else {
          console.log('Some other kind of source/device: ', deviceInfo);
        }
      }

      console.log(context.getters.videoInputDevices,
        context.getters.audioInputDevices,
        context.getters.audioOutputDevices);
    },
    setChosenVideoInputDeviceId(context, value) {
      context.commit('setChosenVideoInputDeviceId', value);
      context.dispatch('initMediaStream');
    },
    setChosenAudioInputDeviceId(context, value) {
      context.commit('setChosenAudioInputDeviceId', value);
      context.dispatch('initMediaStream');
    },
    setChosenAudioOutputDeviceId(context, value) {
      context.commit('setChosenAudioOutputDeviceId', value);
      context.dispatch('initMediaStream');
    },
  },
  mutations: {
    addVideoInputDevice(state, value) {
      state.videoInputDevices.push(value);
    },
    addAudioInputDevice(state, value) {
      state.audioInputDevices.push(value);
    },
    addAudioOutputDevice(state, value) {
      state.audioOutputDevices.push(value);
    },
    clearDevices(state) {
      state.videoInputDevices = [];
      state.audioInputDevices = [];
      state.audioOutputDevices = [];
    },
    setChosenVideoInputDeviceId(state, value) {
      state.chosenVideoInputDeviceId = value;
    },
    setChosenAudioInputDeviceId(state, value) {
      state.chosenAudioInputDeviceId = value;
    },
    setChosenAudioOutputDeviceId(state, value) {
      state.chosenAudioOutputDeviceId = value;
    },
  },
  getters: {
    videoInputDevices: (state) => state.videoInputDevices,
    audioInputDevices: (state) => state.audioInputDevices,
    audioOutputDevices: (state) => state.audioOutputDevices,
    chosenVideoInputDeviceId: (state) => state.chosenVideoInputDeviceId,
    chosenAudioOutputDeviceId: (state) => state.chosenAudioOutputDeviceId,
    chosenAudioInputDeviceId: (state) => state.chosenAudioInputDeviceId,
  },
};
