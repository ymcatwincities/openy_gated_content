import personalTrainingChat from '@/store/modules/personalTraining/chat';
import personalTrainingModal from '@/store/modules/personalTraining/modal';
import personalTrainingWebRtc from '@/store/modules/personalTraining/webrtc';
import personalTrainingControls from '@/store/modules/personalTraining/controls';
import dayjs from 'dayjs';

export default {
  state: {
    videoSessionStatus: false,
    personalTrainingId: null,
    personalTrainingDate: null,
  },
  actions: {
    joinVideoSession(context) {
      context.commit('showJoinOptionsModal', false);
      context.commit('setVideoSessionStatus', true);
      context.dispatch('subscribeToACall');
      if (context.state.peerDataConnected) {
        context.dispatch('callPartner');
      }
    },
    leaveVideoSession(context) {
      context.commit('showLeaveMeetingModal', false);
      context.commit('setVideoSessionStatus', false);
      context.dispatch('closeMediaStream').then(() => {
        context.commit('setMicEnabled', true);
        context.commit('setCameraEnabled', true);
      });
    },
  },
  mutations: {
    setVideoSessionStatus(state, value) {
      state.videoSessionStatus = value;
    },
    setPersonalTrainingId(state, value) {
      state.personalTrainingId = value;
    },
    setPersonalTrainingDate(state, value) {
      state.personalTrainingDate = value;
    },
  },
  getters: {
    isJoinedVideoSession: (state) => state.videoSessionStatus,
    isMeetingComplete: (state) => dayjs().isAfter(dayjs(state.personalTrainingDate)),
    personalTrainingId: (state) => state.personalTrainingId,
  },
  modules: {
    personalTrainingChat,
    personalTrainingModal,
    personalTrainingWebRtc,
    personalTrainingControls,
  },
};
