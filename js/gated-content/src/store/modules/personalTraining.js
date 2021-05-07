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
    instructorRole: false,
    instructorName: null,
    customerName: null,
  },
  actions: {
    async setMeetingMetaData(context, payload) {
      context.commit('setInstructorRole', payload.instructorRole);
      context.commit('setPersonalTrainingId', payload.personalTrainingId);
      context.commit('setPersonalTrainingDate', payload.personalTrainingDate);
      context.commit('setInstructorName', payload.instructorName);
      context.commit('setCustomerName', payload.customerName);
    },
  },
  mutations: {
    setVideoSessionStatus(state, value) {
      state.videoSessionStatus = value;
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
    setPersonalTrainingId(state, value) {
      state.personalTrainingId = value;
    },
    setPersonalTrainingDate(state, value) {
      state.personalTrainingDate = value;
    },
  },
  getters: {
    isJoinedVideoSession: (state) => state.videoSessionStatus,
    isMeetingComplete: (state) => dayjs().isAfter(state.personalTrainingDate),
    personalTrainingId: (state) => state.personalTrainingId,
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
