export default {
  state: {
    signalingServerConnection: null,
    signalingServerConnected: false,
  },
  actions: {
    async connectToSignalingServer(context) {
      const serverPRL = context.getters.getAppSettings.signalingServerPRL;
      if (!serverPRL || serverPRL.length === 0) {
        context.dispatch('debugLog', ['WebRTC signaling server URL is not provided.']);
        return;
      }

      const { personalTrainingId, isInstructorRole } = context.getters;
      const ws = new WebSocket(`wss://${serverPRL}?meetingId=${personalTrainingId}&isInstructorRole=${isInstructorRole}`);

      ws.addEventListener('message', (event) => {
        if (event.data === 'ready') {
          if (context.getters.isInstructorRole) {
            context.dispatch('initPeer');
          }
        } else {
          context.dispatch('receiveSignalingMessage', JSON.parse(event.data));
        }
      });

      ws.addEventListener('open', () => {
        context.commit('setSignalingServerConnected', true);
        context.dispatch('debugLog', ['VY Signaling Server connected.  initialize webrtc..']);
        if (!context.getters.isInstructorRole) {
          context.dispatch('initPeer');
        }
      });

      ws.addEventListener('close', () => {
        context.commit('setSignalingServerConnected', false);
        // eslint-disable-next-line no-undef
        _.delay(() => {
          context.dispatch('connectToSignalingServer');
        }, 1000);
      });

      context.commit('setSignalingServerConnection', ws);
    },
    async sendSignalingMessage(context, message) {
      context.dispatch('debugLog', ['signal send ->:', message]);
      context.getters.signalingServerConnection.send(JSON.stringify(message));
    },
    async receiveSignalingMessage(context, signal) {
      context.dispatch('debugLog', ['signal receive <-:', signal]);
      if (context.getters.peer) {
        context.getters.peer.signal(signal);
      }
    },
  },
  mutations: {
    setSignalingServerConnection(state, value) {
      state.signalingServerConnection = value;
    },
    setSignalingServerConnected(state, value) {
      state.signalingServerConnected = value;
    },
  },
  getters: {
    signalingServerConnection: (state) => state.signalingServerConnection,
    signalingServerConnected: (state) => state.signalingServerConnected,
  },
};
