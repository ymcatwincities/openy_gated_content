import events from '@/store/modules/personalTraining/webrtc/events';
import signalingServer from '@/store/modules/personalTraining/webrtc/signalingServer';
import peerConnection from '@/store/modules/personalTraining/webrtc/peerConnection';
import mediaStream from '@/store/modules/personalTraining/webrtc/mediaStream';
import deviceManager from '@/store/modules/personalTraining/webrtc/deviceManager';

export default {
  actions: {
    joinVideoSession(context) {
      context.commit('showJoinOptionsModal', false);
      context.commit('setVideoSessionStatus', true);
      context.dispatch('callPartner');
    },
    leaveVideoSession(context) {
      context.commit('showLeaveMeetingModal', false);
      context.commit('setVideoSessionStatus', false);
      context.dispatch('closeLocalMediaStream')
        .then(() => context.dispatch('sendCallEndedEvent'))
        .then(() => {
          context.commit('setMicEnabled', true);
          context.commit('setCameraEnabled', true);
        });
    },
    callPartner(context) {
      if (!context.getters.peerConnected) {
        return;
      }
      context.dispatch('debugLog', ['call partner']);
      context.dispatch('sendLocalCamEnabledState', context.getters.isCameraEnabled);
      context.dispatch('sendLocalMicEnabledState', context.getters.isMicEnabled);
      context.getters.peer.addStream(context.getters.localMediaStream);
    },
  },
  modules: {
    events,
    signalingServer,
    peerConnection,
    mediaStream,
    deviceManager,
  },
};
