import liveChat from '@/store/modules/liveChat/chat';
import liveChatModal from '@/store/modules/liveChat/modal';
import liveChatRatchet from '@/store/modules/liveChat/ratchet';
import client from '@/client';

export default {
  state: {
    liveChatMeetingId: false,
    liveChatMeetingTitle: null,
    liveChatMeetingStart: null,
    liveChatMeetingDate: null,
    liveChatLocalName: null,
    liveChatUserId: null,
    liveChatRatchetConfigs: null,
  },
  actions: {
    async setLiveChatData(context, payload) {
      context.commit('setLiveChatMeetingId', payload.liveChatMeetingId);
      context.commit('setLiveChatMeetingTitle', payload.liveChatMeetingTitle);
      context.commit('setLiveChatMeetingStart', payload.liveChatMeetingStart);
      context.commit('setLiveChatMeetingDate', payload.liveChatMeetingDate);
      context.commit('setLiveChatLocalName', payload.liveChatLocalName);
      context.commit('setLiveChatUserId', payload.liveChatUserId);
      context.commit('setLiveChatRatchetConfigs', payload.liveChatRatchetConfigs);
    },
    async updateLiveChatLocalName(context, payload) {
      return client({
        url: 'livechat/update-user-name',
        method: 'post',
        params: {
          _format: 'json',
        },
        data: {
          name: payload,
        },
      }).then(() => {
        context.commit('setLiveChatLocalName', payload);
      });
    },
  },
  mutations: {
    setLiveChatMeetingId(state, value) {
      state.liveChatMeetingId = value;
    },
    setLiveChatMeetingTitle(state, value) {
      state.liveChatMeetingTitle = value;
    },
    setLiveChatMeetingStart(state, value) {
      state.liveChatMeetingStart = value;
    },
    setLiveChatMeetingDate(state, value) {
      state.liveChatMeetingDate = value;
    },
    setLiveChatLocalName(state, value) {
      state.liveChatLocalName = value;
    },
    setLiveChatUserId(state, value) {
      state.liveChatUserId = value;
    },
    setLiveChatRatchetConfigs(state, value) {
      state.liveChatRatchetConfigs = value;
    },
  },
  getters: {
    liveChatMeetingId: (state) => state.liveChatMeetingId,
    liveChatMeetingTitle: (state) => state.liveChatMeetingTitle,
    liveChatMeetingStart: (state) => state.liveChatMeetingStart,
    liveChatLocalName: (state) => state.liveChatLocalName,
    liveChatUserId: (state) => state.liveChatUserId,
    liveChatRatchetConfigs: (state) => state.liveChatRatchetConfigs,
  },
  modules: {
    liveChat,
    liveChatModal,
    liveChatRatchet,
  },
};
