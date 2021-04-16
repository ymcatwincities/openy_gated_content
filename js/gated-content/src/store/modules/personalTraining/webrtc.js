import client from '@/client';

export default {
  state: {
    peer: null,
    peerOpened: false,
    customerPeerId: null,
    instructorPeerId: null,
    customerPeerSource: null,
    instructorMediaStream: null,
    customerMediaStream: null,
    peerDataConnected: false,
    peerDataConnection: null,
    peerStreamConnected: false,
    peerMediaConnection: null,
    instructorRole: false,
    instructorName: null,
    customerName: null,
  },
  actions: {
    async initPeer(context, payload) {
      if (context.state.peer !== null
        || context.state.personalTrainingId === payload.personalTrainingId) {
        return;
      }

      context.commit('setInstructorRole', payload.instructorRole);
      context.commit('setPersonalTrainingId', payload.personalTrainingId);
      context.commit('setPersonalTrainingDate', payload.personalTrainingDate);
      context.commit('setInstructorName', payload.instructorName);
      context.commit('setCustomerName', payload.customerName);

      let peerId = null;
      if (!payload.instructorRole && payload.customerPeerId) {
        peerId = payload.customerPeerId;
      }

      // @TODO implement configuration load from settings
      // eslint-disable-next-line no-undef
      const peer = new Peer(peerId, {
        debug: 3,
        host: '9000-olive-silkworm-d37irlao.ws-eu03.gitpod.io',
        port: 443,
        path: '/',
        secure: true,
        config: {
          iceServers: [
            { url: 'stun:stun1.l.google.com:19302' },
            {
              url: 'turn:numb.viagenie.ca',
              credential: 'muazkh',
              username: 'webrtc@live.com',
            },
          ],
        },
      });
      context.commit('setPeer', peer);

      peer.on('open', (id) => {
        context.commit('setPeerOpened', true);

        if (context.state.instructorRole) {
          if (context.state.customerPeerId) {
            context.dispatch('connectToCustomerPeer');
          } else {
            context.dispatch('loadCustomerPeer');
          }
        } else {
          context.commit('setCustomerPeerId', id);
          if (peerId === null) {
            context.dispatch('publishCustomerPeer');
          }
        }
      });

      peer.on('close', () => {
        context.commit('setPeerOpened', false);
      });

      peer.on('disconnected', () => {
        context.commit('setPeerOpened', false);
      });

      peer.on('connection', (dataConnection) => {
        context.dispatch('handleDataConnection', dataConnection);

        if (context.getters.isJoinedVideoSession) {
          context.dispatch('callPartner');
        }
      });

      peer.on('error', (error) => {
        console.log('peer error', error.type, error);
        if (error.type === 'peer-unavailable') {
          context.dispatch('connectToCustomerPeer');
        }
      });
    },
    async initMediaStream(context) {
      navigator.mediaDevices.getUserMedia({
        audio: {
          echoCancellation: true,
          noiseSuppression: true,
          autoGainControl: false,
        },
        video: true,
      })
        .then((mediaStream) => {
          context.dispatch('setLocalMediaStream', mediaStream);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    async setLocalMediaStream(context, mediaStream) {
      if (context.state.instructorRole) {
        context.commit('setInstructorMediaStream', mediaStream);
      } else {
        context.commit('setCustomerMediaStream', mediaStream);
      }
    },
    async closeMediaStream(context) {
      if (context.getters.localMediaStream !== null) {
        context.getters.localMediaStream.getTracks().forEach((track) => {
          track.stop();
        });
        context.dispatch('setLocalMediaStream', null);
      }
      if (context.state.peerMediaConnection !== null) {
        context.commit('setPeerMediaConnection', null);
      }
    },
    async publishCustomerPeer(context) {
      client.get('personal-training/publish-customer-peer', {
        params: {
          trainingId: context.getters.personalTrainingId,
          peerId: context.getters.customerPeerId,
        },
      });
    },
    async loadCustomerPeer(context) {
      client.get('personal-training/load-customer-peer', {
        params: {
          trainingId: context.getters.personalTrainingId,
        },
      }).then((response) => {
        const peerId = response.data;
        if (peerId) {
          context.commit('setCustomerPeerId', peerId);
          context.dispatch('connectToCustomerPeer');
        } else {
          // eslint-disable-next-line no-undef
          _.delay(() => {
            context.dispatch('loadCustomerPeer');
          }, 2000);
        }
      });
    },
    async connectToCustomerPeer(context) {
      const dataConnection = context.state.peer.connect(context.state.customerPeerId);
      context.dispatch('handleDataConnection', dataConnection);
    },
    async handleDataConnection(context, dataConnection) {
      context.dispatch('setPartnerMediaStream', null);
      context.commit('setPeerDataConnected', true);
      context.commit('setPeerDataConnection', dataConnection);
      context.dispatch('setPartnerPeerId', dataConnection.peer);
      dataConnection.on('open', () => {
        context.commit('setPeerDataConnected', true);
        context.commit('setPeerDataConnection', dataConnection);
      });
      dataConnection.on('data', (data) => {
        context.dispatch('receiveChatMessage', data);
      });
      dataConnection.on('close', () => {
        context.commit('setPeerDataConnected', false);
        context.commit('setPeerDataConnection', null);
        context.dispatch('setPartnerMediaStream', null);
        if (context.state.instructorRole) {
          context.dispatch('connectToCustomerPeer');
        }
      });
      dataConnection.on('error', (error) => {
        console.log('dataConnection error:', error);
      });
    },
    async subscribeToACall(context) {
      context.state.peer.on('call', (call) => {
        call.answer(context.getters.localMediaStream);
        context.commit('setPeerMediaConnection', call);
        call.on('stream', (stream) => {
          context.commit('setPeerStreamConnected', true);
          context.dispatch('setPartnerMediaStream', stream);
        });
        call.on('close', () => {
          context.commit('setPeerStreamConnected', false);
          context.dispatch('setPartnerMediaStream', null);
        });
      });
    },
    async callPartner(context) {
      const call = context.state.peer.call(
        context.getters.partnerPeerId,
        context.getters.localMediaStream,
      );
      call.on('stream', (stream) => {
        context.commit('setPeerStreamConnected', true);
        context.dispatch('setPartnerMediaStream', stream);
      });
      call.on('close', () => {
        context.commit('setPeerStreamConnected', false);
        context.dispatch('setPartnerMediaStream', null);
      });
      call.on('error', (error) => {
        console.log(error);
      });
    },
    setPartnerPeerId(context, peerId) {
      if (context.state.instructorRole) {
        context.commit('setCustomerPeerId', peerId);
      } else {
        context.commit('setInstructorPeerId', peerId);
      }
    },
    setPartnerMediaStream(context, value) {
      if (context.state.instructorRole) {
        context.commit('setCustomerMediaStream', value);
      } else {
        context.commit('setInstructorMediaStream', value);
      }
    },
  },
  mutations: {
    setPeer(state, value) {
      state.peer = value;
    },
    setCustomerPeerId(state, peerId) {
      state.customerPeerId = peerId;
    },
    setInstructorPeerId(state, peerId) {
      state.instructorPeerId = peerId;
    },
    setCustomerPeerSource(state, source) {
      state.customerPeerSource = source;
    },
    setPeerOpened(state, value) {
      state.peerOpened = value;
    },
    setPeerDataConnected(state, value) {
      state.peerDataConnected = value;
    },
    setPeerDataConnection(state, value) {
      state.peerDataConnection = value;
    },
    setPeerStreamConnected(state, value) {
      state.peerStreamConnected = value;
    },
    setPeerMediaConnection(state, value) {
      state.peerMediaConnection = value;
    },
    setInstructorMediaStream(state, value) {
      state.instructorMediaStream = value;
    },
    setCustomerMediaStream(state, value) {
      state.customerMediaStream = value;
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
    peer: (state) => state.peer,
    partnerPeerId: (state) => (
      state.instructorRole
        ? state.customerPeerId
        : state.instructorPeerId),
    localPeerId: (state) => (
      state.instructorRole
        ? state.instructorPeerId
        : state.customerPeerId),
    customerPeerId: (state) => state.customerPeerId,
    localMediaStream: (state) => (state.instructorRole
      ? state.instructorMediaStream
      : state.customerMediaStream),
    partnerMediaStream: (state) => (
      state.instructorRole
        ? state.customerMediaStream
        : state.instructorMediaStream),
    customerMediaStream: (state) => state.customerMediaStream,
    instructorMediaStream: (state) => state.instructorMediaStream,
    isInstructorRole: (state) => state.instructorRole,
    localName: (state) => (state.instructorRole
      ? state.instructorName
      : state.customerName),
    partnerName: (state) => (
      state.instructorRole
        ? state.customerName
        : state.instructorName),
  },
};
