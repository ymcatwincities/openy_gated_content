import personalTrainingChat from '@/store/modules/personalTraining/chat';
import personalTrainingModal from '@/store/modules/personalTraining/modal';
import personalTrainingWebRtc from '@/store/modules/personalTraining/webrtc';
import personalTrainingControls from '@/store/modules/personalTraining/controls';

export default {
  state: {
    videoSessionStatus: false,
    personalTrainingId: null,
    personalTrainingDate: null,
    instructorRole: false,
    instructorName: null,
    customerName: null,
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
      context.dispatch('closeMediaStream');
      context.commit('setMicEnabled', true);
      context.commit('setCameraEnabled', true);
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
    setInstructorRole(state, value) {
      state.instructorRole = value;
    },
    setInstructorName(state, value) {
      state.instructorName = value;
    },
    setCustomerName(state, value) {
      state.customerName = value;
    },
  },
  getters: {
    isJoinedVideoSession: (state) => state.videoSessionStatus,
    isInstructorRole: (state) => state.instructorRole,
    localName: (state) => (state.instructorRole
      ? state.instructorName
      : state.customerName),
    partnerName: (state) => (
      state.instructorRole
        ? state.customerName
        : state.instructorName),
  },
  modules: {
    personalTrainingChat,
    personalTrainingModal,
    personalTrainingWebRtc,
    personalTrainingControls,
  },
};
