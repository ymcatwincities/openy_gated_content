export default {
  actions: {
    async sendCallEndedEvent(context) {
      context.dispatch('sendPeerData', { action: 'closeRemoteMediaStream' });
    },
    async sendLocalCamEnabledState(context, value) {
      context.dispatch('sendPeerData', { action: 'setPartnerCamEnabled', payload: value });
    },
    async sendLocalMicEnabledState(context, value) {
      context.dispatch('sendPeerData', { action: 'setPartnerMicEnabled', payload: value });
    },
  },
};
