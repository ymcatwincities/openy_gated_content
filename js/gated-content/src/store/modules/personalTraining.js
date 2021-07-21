import personalTrainingChat from '@/store/modules/personalTraining/chat';
import personalTrainingModal from '@/store/modules/personalTraining/modal';
import personalTrainingWebRtc from '@/store/modules/personalTraining/webrtc';
import personalTrainingControls from '@/store/modules/personalTraining/controls';
import client from '@/client';
import dayjs from 'dayjs';

export default {
  state: {
    videoSessionStatus: false,
    personalTrainingId: null,
    personalTrainingDate: null,
    remoteLink: '',
    instructorRole: false,
    instructorName: null,
    customerName: null,
    localName: null,
  },
  actions: {
    async setMeetingMetaData(context, payload) {
      context.commit('setInstructorRole', payload.instructorRole);
      context.commit('setPersonalTrainingId', payload.personalTrainingId);
      context.commit('setPersonalTrainingDate', payload.personalTrainingDate);
      context.commit('setInstructorName', payload.instructorName);
      context.commit('setCustomerName', payload.customerName);
      context.commit('setRemoteLink', payload.remoteLink);
    },
    async updateLocalName(context, payload) {
      return client({
        url: 'personal-training/update-user-name',
        method: 'post',
        params: {
          _format: 'json',
        },
        data: {
          name: payload,
        },
      })
        .then(() => {
          context.commit('setLocalName', payload);
        });
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
    setRemoteLink(state, value) {
      state.remoteLink = value;
    },
    setLocalName(state, value) {
      state.localName = value;
    },
  },
  getters: {
    isJoinedVideoSession: (state) => state.videoSessionStatus,
    isMeetingComplete: (state) => dayjs().isAfter(state.personalTrainingDate),
    personalTrainingId: (state) => state.personalTrainingId,
    isInstructorRole: (state) => state.instructorRole,
    remoteLink: (state) => state.remoteLink,
    localName: (state) => state.localName,
    systemName: (state) => (state.instructorRole
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
