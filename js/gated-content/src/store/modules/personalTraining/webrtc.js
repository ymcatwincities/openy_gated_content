import events from '@/store/modules/personalTraining/webrtc/events';
import signalingServer from '@/store/modules/personalTraining/webrtc/signalingServer';
import peerConnection from '@/store/modules/personalTraining/webrtc/peerConnection';

export default {
  state: {
    peer: null,
    peerInitializationError: '',
    customerPeerId: null,
    instructorPeerId: null,
    customerPeerSource: null,
    instructorMediaStream: null,
    customerMediaStream: null,
    peerStreamConnected: false,
    peerMediaConnection: null,
    instructorRole: false,
    instructorName: null,
    customerName: null,
  },
  actions: {
    async setMeetingMetaData(context, payload) {
      context.commit('setInstructorRole', payload.instructorRole);
      context.commit('setPersonalTrainingId', payload.personalTrainingId);
      context.commit('setPersonalTrainingDate', payload.personalTrainingDate);
      context.commit('setInstructorName', payload.instructorName);
      context.commit('setCustomerName', payload.customerName);
    },
    async initPeer(context) {
      console.log('initPeer');
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
      console.log(peer);
      context.commit('setPeer', peer);

      peer.on('signal', (data) => {
        context.dispatch('sendSignalingMessage', data);
      });

      peer.on('connect', () => {
        context.commit('setPeerConnected', true);
        console.log('peer on connect');
        if (context.getters.isJoinedVideoSession) {
          context.dispatch('callPartner');
        }
      });

      peer.on('stream', (stream) => {
        console.log('stream received <-', stream);
        context.commit('setPeerStreamConnected', true);
        context.dispatch('setPartnerMediaStream', stream);
        context.dispatch('sendVideoStateEvent', context.getters.isCameraEnabled);
      });

      peer.on('data', (dataString) => {
        const data = JSON.parse(dataString);
        console.log('peer on data', data);
        if (data.newMessage) {
          context.dispatch('receiveChatMessage', data.newMessage);
        } else if (data.videoStateEvent) {
          context.dispatch('setRemoteVideoStateEvent', data.videoStateEvent);
        } else if (data === 'callEndedEvent') {
          context.dispatch('callEndedEvent');
        }
      });

      peer.on('close', () => {
        console.log('peer on close');
        context.commit('setPeerConnected', false);
        context.getters.peer.destroy();
        context.dispatch('initPeer');
      });

      //
      // peer.on('disconnected', () => {
      //   console.log('peer disconnected');
      //   context.commit('setPeerSignalingServerConnected', false);
      //   // eslint-disable-next-line no-undef
      //   _.delay(() => {
      //     peer.reconnect();
      //   }, 1000);
      // });

      peer.on('error', (error) => {
        console.log('peer error', error, error.code);

        if (error.code === peer.ERR_CONNECTION_FAILURE) {
          if (context.getters.isInstructorRole) {
            context.dispatch('loadCustomerPeer');
          }
        } else if (error.code === peer.ERR_WEBRTC_SUPPORT) {
          context.commit('setPeerInitializationError', 'Your browser does not support video meeting features that you are trying to use.');
        } else {
          console.log('unhandled peer error', error);
        }
      });
    },
    async initMediaStream(context) {
      await navigator.mediaDevices.getUserMedia({
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
          console.log('init local stream', error);
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
      console.log('close media connection');
      if (context.state.peerMediaConnection !== null) {
        if (context.state.peerMediaConnection.close) {
          context.state.peerMediaConnection.close();
        }
        context.commit('setPeerStreamConnected', false);
        context.commit('setPeerMediaConnection', null);
        context.dispatch('setPartnerMediaStream', null);
      }
    },
    async sendData(context, data) {
      if (context.getters.peerConnected) {
        context.state.peer.send(JSON.stringify(data));
      }
    },
    // async handleMediaConnection(context, mediaConnection) {
    //   console.log('handle media connection', mediaConnection);
    //   mediaConnection.on('stream', (stream) => {
    //     context.commit('setPeerStreamConnected', true);
    //     context.dispatch('setPartnerMediaStream', stream);
    //     context.dispatch('sendVideoStateEvent', context.getters.isCameraEnabled);
    //   });
    //
    //   mediaConnection.on('close', () => {
    //     context.commit('setPeerStreamConnected', false);
    //     context.dispatch('setPartnerMediaStream', null);
    //   });
    //
    //   mediaConnection.on('error', (error) => {
    //     console.log('media connection', error);
    //   });
    //
    //   context.commit('setPeerMediaConnection', mediaConnection);
    // },
    // async subscribeToACall(context) {
    //   // context.state.peer.on('call', (mediaConnection) => {
    //   //   console.log('received a call');
    //   //   context.dispatch('handleMediaConnection', mediaConnection);
    //   //   mediaConnection.answer(context.getters.localMediaStream);
    //   // });
    // },
    async callPartner(context) {
      console.log('call partner');
      context.getters.peer.addStream(context.getters.localMediaStream);
      // const mediaConnection = context.state.peer.call(
      //   context.getters.partnerPeerId,
      //   context.getters.localMediaStream,
      // );
      //
      // await context.dispatch('handleMediaConnection', mediaConnection);

      // // eslint-disable-next-line no-undef
      // _.delay(() => {
      //   context.dispatch('recallPartner');
      // }, 1000);
    },
    // async recallPartner(context) {
    //   if (context.getters.peerDataConnected
    //     && context.getters.isJoinedVideoSession
    //     && context.getters.peerMediaConnection !== null
    //     && !context.getters.peerMediaConnection.open) {
    //     context.getters.peerMediaConnection.close();
    //     context.commit('setPeerMediaConnection', null);
    //     context.dispatch('callPartner');
    //   }
    // },
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
    setPeerInitializationError(state, value) {
      state.peerInitializationError = value;
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
    peerInitializationError: (state) => state.peerInitializationError,
    peerMediaConnection: (state) => state.peerMediaConnection,
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
    events,
    signalingServer,
    peerConnection,
  },
};
