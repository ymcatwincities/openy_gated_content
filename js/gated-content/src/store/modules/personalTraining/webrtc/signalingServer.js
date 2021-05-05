export default {
  state: {
    signalingServerConnection: null,
    signalingServerConnected: false,
  },
  actions: {
    async connectToSignalingServer(context) {
      const serverPRL = context.getters.getAppSettings.signalingServerPRL;
      if (!serverPRL || serverPRL.length === 0) {
        console.log('WebRTC signaling server URL is not provided.');
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
        console.log('connected to signaling server. initialize webrtc..');
        if (!context.getters.isInstructorRole) {
          context.dispatch('initPeer');
        }
      });

      ws.addEventListener('close', () => {
        context.commit('setSignalingServerConnected', false);
        context.dispatch('setPartnerMediaStream', null);
      });

      context.commit('setSignalingServerConnection', ws);
    },
    async sendSignalingMessage(context, message) {
      console.log('send signal ->:', message);
      context.getters.signalingServerConnection.send(JSON.stringify(message));
    },
    async receiveSignalingMessage(context, signal) {
      console.log('signal receive <-:', signal);
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
