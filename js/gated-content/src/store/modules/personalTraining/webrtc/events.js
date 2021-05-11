export default {
  actions: {
    async sendCallEndedEvent(context) {
      context.dispatch('sendPeerData', { action: 'closeRemoteMediaStream' });
    },
    async sendLocalCamEnabledState(context, payload) {
      context.dispatch('sendPeerData', { action: 'setPartnerCamEnabled', payload });
    },
    async sendLocalMicEnabledState(context, payload) {
      context.dispatch('sendPeerData', { action: 'setPartnerMicEnabled', payload });
    },
  },
};
