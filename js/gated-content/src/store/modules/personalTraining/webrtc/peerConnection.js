export default {
  state: {
    peer: null,
    peerConnected: false,
    peerInitializationError: '',
  },
  actions: {
    async initPeer(context) {
      context.dispatch('debugLog', ['VY Simple Peer', 'initPeer']);
      // eslint-disable-next-line no-undef
      if (SimplePeer === undefined) {
        // eslint-disable-next-line no-undef
        _.delay(() => {
          context.dispatch('initPeer');
        }, 1000);
        return;
      }

      let peerjsSTUNUrl;
      let peerjsTURNUrl;
      let peerjsTURNUsername;
      let peerjsTURNCredential;

      ({
        // eslint-disable-next-line prefer-const
        peerjsSTUNUrl,
        // eslint-disable-next-line prefer-const
        peerjsTURNUrl, peerjsTURNUsername, peerjsTURNCredential,
        // eslint-disable-next-line prefer-const
      } = context.getters.getAppSettings);

      const config = {};

      if (peerjsSTUNUrl !== '') {
        config.iceServers = [
          { urls: [peerjsSTUNUrl] },
          {
            urls: [peerjsTURNUrl],
            username: peerjsTURNUsername,
            credential: peerjsTURNCredential,
          },
        ];
      }

      // eslint-disable-next-line no-undef
      const peer = new SimplePeer({
        initiator: context.getters.isInstructorRole === true,
        config,
      });
      context.commit('setPeer', peer);

      peer.on('signal', (data) => {
        context.dispatch('sendSignalingMessage', data);
      });

      peer.on('connect', () => {
        context.commit('setPeerConnected', true);
        context.dispatch('debugLog', ['Peer on connect']);
        if (context.getters.isJoinedVideoSession) {
          context.dispatch('callPartner');
        }
      });

      peer.on('stream', (stream) => {
        context.dispatch('debugLog', ['stream received <-', stream]);
        context.commit('setPeerStreamConnected', true);
        context.dispatch('setPartnerMediaStream', stream);
      });

      peer.on('data', (dataString) => {
        const data = JSON.parse(dataString);
        context.dispatch('debugLog', ['Peer on data', data]);
        context.dispatch(data.action, data.payload);
      });

      peer.on('close', () => {
        context.dispatch('debugLog', ['Peer on close']);
        context.commit('setPeerConnected', false);
        context.dispatch('closeRemoteMediaStream');
        context.getters.peer.destroy();
        // eslint-disable-next-line no-undef
        _.delay(() => {
          context.dispatch('initPeer');
        }, 1000);
      });

      peer.on('error', (error) => {
        context.dispatch('debugLog', ['Peer error:', error, error.code]);
        if (error.code === peer.ERR_WEBRTC_SUPPORT) {
          context.commit('setPeerInitializationError', 'Your browser does not support video meeting features that you are trying to use.');
        } else {
          context.dispatch('debugLog', ['Unhandled peer error:', error]);
        }
      });
    },
    async sendPeerData(context, data) {
      if (context.getters.peerConnected) {
        context.getters.peer.send(JSON.stringify(data));
      }
    },
  },
  mutations: {
    setPeer(state, value) {
      state.peer = value;
    },
    setPeerConnected(state, value) {
      state.peerConnected = value;
    },
    setPeerInitializationError(state, value) {
      state.peerInitializationError = value;
    },
  },
  getters: {
    peer: (state) => state.peer,
    peerConnected: (state) => state.peerConnected,
    peerInitializationError: (state) => state.peerInitializationError,
  },
};
