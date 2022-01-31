import dayjs from 'dayjs';
import liveChat from '@/store/modules/liveChat/chat';
import liveChatModal from '@/store/modules/liveChat/modal';
import liveChatRatchet from '@/store/modules/liveChat/ratchet';
import client from '@/client';

export default {
  state: {
    liveChatMeetingId: false,
    liveChatMeetingDate: null,
    liveChatLocalName: null,
  },
  actions: {
    async setLiveChatMetaData(context, payload) {
      context.commit('setLiveChatMeetingId', payload.liveChatMeetingId);
      context.commit('setLiveChatMeetingDate', payload.liveChatMeetingDate);
    },
    async updateLiveChatLocalName(context, payload) {
      return client({
        url: 'livechat/update-user-name',
        method: 'post',
        params: {
          _format: 'json',
        },
        data: {
          liveStreamName: payload,
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
    setLiveChatMeetingDate(state, value) {
      state.liveChatMeetingDate = value;
    },
    setLiveChatLocalName(state, value) {
      state.liveChatLocalName = value;
    },
  },
  getters: {
    isLiveChatMeetingComplete: (state) => dayjs().isAfter(state.liveChatMeetingDate),
    liveChatMeetingId: (state) => state.liveChatMeetingId,
    liveChatLocalName: (state) => state.liveChatLocalName,
  },
  modules: {
    liveChat,
    liveChatModal,
    liveChatRatchet,
  },
};
