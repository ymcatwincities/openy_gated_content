import client from '@/client';
import personalTrainingWebRtcEvents from '@/store/modules/personalTraining/webrtc/events';

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

      const config = {
        debug: 3,
        secure: true,
        config: {
          iceServers: [
            { url: 'stun:stun.l.google.com:19302' },
            {
              url: 'turn:192.158.29.39:3478?transport=udp',
              credential: 'JZEOEt2V3Qb0y27GRntt2u2PAYA=',
              username: '28224511:1379330808',
            },
          ],
        },
      };

      if (context.getters.getAppSettings.peerjs_domain
        && context.getters.getAppSettings.peerjs_domain.length > 0) {
        config.host = context.getters.getAppSettings.peerjs_domain;
      }

      if (context.getters.getAppSettings.peerjs_port
        && context.getters.getAppSettings.peerjs_port.length > 0) {
        config.port = context.getters.getAppSettings.peerjs_port;
      }

      if (context.getters.getAppSettings.peerjs_uri
        && context.getters.getAppSettings.peerjs_uri.length > 0) {
        config.path = context.getters.getAppSettings.peerjs_uri;
      }

      // eslint-disable-next-line no-undef
      const peer = new Peer(peerId, config);
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
        video: {
          width: { min: 640, ideal: 1920, max: 1920 },
          height: { min: 400, ideal: 1080 },
          aspectRatio: 1.777777778,
        },
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
      context.dispatch('closeMediaConnection');
    },
    async closeMediaConnection(context) {
      console.log('closeMediaConnection 1');
      console.log(context.state.peerMediaConnection);
      if (context.state.peerMediaConnection !== null) {
        console.log('closeMediaConnection 2');
        if (context.state.peerMediaConnection.close) {
          context.state.peerMediaConnection.close();
        }
        context.commit('setPeerStreamConnected', false);
        context.commit('setPeerMediaConnection', null);
        context.dispatch('setPartnerMediaStream', null);
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
        console.log(data);
        if (data.newMessage) {
          context.dispatch('receiveChatMessage', data.newMessage);
        } else if (data.videoStateEvent) {
          context.dispatch('setRemoteVideoStateEvent', data.videoStateEvent);
        } else if (data === 'callEndedEvent') {
          context.dispatch('callEndedEvent');
        }
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
          context.dispatch('sendVideoStateEvent', context.getters.isCameraEnabled);
          context.commit('setPeerStreamConnected', true);
          context.dispatch('setPartnerMediaStream', stream);
        });
        call.on('close', () => {
          context.commit('setPeerStreamConnected', false);
          context.dispatch('setPartnerMediaStream', null);
        });
      });
    },
    async sendData(context, data) {
      if (context.getters.peerDataConnection) {
        context.getters.peerDataConnection.send(data);
      }
    },
    async callPartner(context) {
      const call = context.state.peer.call(
        context.getters.partnerPeerId,
        context.getters.localMediaStream,
      );
      context.commit('setPeerMediaConnection', call);
      call.on('stream', (stream) => {
        context.dispatch('sendVideoStateEvent', context.getters.isCameraEnabled);
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
    peerDataConnected: (state) => state.peerDataConnected,
    peerDataConnection: (state) => state.peerDataConnection,
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
    partnerMediaStream: (state) => (state.instructorRole
      ? state.customerMediaStream
      : state.instructorMediaStream),
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
    personalTrainingWebRtcEvents,
  },
};
