import client from '@/client';

export default {
  state: {
    videoSessionStatus: false,
    micEnabled: true,
    cameraEnabled: true,
    view: 'horizontal',
    fullScreenMode: false,
    chatSession: [
      {
        author: 'user1',
        message: 'msg1',
        date: new Date(),
      },
      {
        author: 'user2',
        message: 'msg2',
        date: new Date(),
      },
      {
        author: 'user1',
        message: 'msg3',
        date: new Date(),
      },
    ],
    showJoinOptionsModal: false,
    showViewOptionsModal: false,
    showChatModal: false,
    showLeaveMeetingModal: false,
    peer: null,
    customerPeerId: null,
    customerPeerSource: null,
    peerOpened: false,
    peerDataConnected: false,
    peerDataConnection: null,
    peerStreamConnected: false,
    peerMediaConnection: null,
    instructorMediaStream: null,
    customerMediaStream: null,
    instructorRole: false,
    personalTrainigId: null,
  },
  actions: {
    addChatMessage(context, message) {
      const msgObj = {
        author: 'user2',
        message,
        date: new Date(),
      };
      context.commit('addChatMessage', msgObj);
    },
    setHorizontalView(context) {
      context.commit('showViewOptionsModal', false);
      context.commit('setView', 'horizontal');
    },
    setVerticalView(context) {
      context.commit('showViewOptionsModal', false);
      context.commit('setView', 'vertical');
    },
    setInsetView(context) {
      context.commit('showViewOptionsModal', false);
      context.commit('setView', 'inset');
    },
    toggleMicEnabled(context) {
      context.commit('setMicEnabled', !context.state.micEnabled);
    },
    toggleCameraEnabled(context) {
      context.commit('setCameraEnabled', !context.state.cameraEnabled);
    },
    toggleFullScreenMode(context) {
      context.commit('toggleFullScreenMode', !context.state.fullScreenMode);
    },
    toggleShowChatModal(context) {
      context.commit('showChatModal', !context.state.showChatModal);
    },
    toggleShowJoinOptionsModal(context) {
      context.commit('showJoinOptionsModal', !context.state.showJoinOptionsModal);

      if (context.state.showJoinOptionsModal) {
        context.dispatch('initMediaStream');
      } else {
        context.dispatch('closeMediaStream');
      }
    },
    toggleShowLeaveMeetingModal(context) {
      context.commit('showLeaveMeetingModal', !context.state.showLeaveMeetingModal);
    },
    toggleViewOptionsModal(context) {
      context.commit('showViewOptionsModal', !context.state.showViewOptionsModal);
    },
    joinVideoSession(context) {
      context.commit('showJoinOptionsModal', false);
      context.commit('setVideoSessionStatus', true);
      if (context.state.instructorRole) {
        context.dispatch('callCustomer');
      } else {
        context.dispatch('subscribeCustomerToCall');
      }
    },
    leaveVideoSession(context) {
      context.commit('showLeaveMeetingModal', false);
      context.commit('setVideoSessionStatus', false);
      context.dispatch('closeMediaStream');
    },
    async initPeer(context, payload) {
      console.log(payload);
      if (context.state.peer !== null
        || context.state.personalTrainigId === payload.personalTrainigId) {
        return;
      }

      context.commit('setInstructorRole', payload.instructorRole);
      context.commit('setPersonalTrainingId', payload.personalTrainingId);

      let peerId = null;
      if (!payload.instructorRole && payload.customerPeerId) {
        peerId = payload.customerPeerId;
      }

      // @TODO implement configuration load from settings
      // eslint-disable-next-line no-undef
      const peer = new Peer(peerId, {
        debug: 3,
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
        context.commit('setPeerDataConnected', true);
        context.commit('setPeerDataConnection', dataConnection);
        dataConnection.on('data', (data) => {
          context.dispatch('receivePeerData', data);
        });
        dataConnection.on('close', () => {
          context.commit('setPeerDataConnected', false);
          context.commit('setPeerDataConnection', null);
        });
      });
    },
    async initMediaStream(context) {
      console.log('initMediaStream');
      // Monkeypatch for crossbrowser geusermedia
      navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia
        || navigator.mozGetUserMedia;

      // Request audio an video
      navigator.getUserMedia({
        audio: context.getters.isMicEnabled,
        video: context.getters.isCameraEnabled,
      }, (mediaStream) => {
        console.log(mediaStream);
        context.dispatch('setOwnMediaStream', mediaStream);
      }, (error) => {
        console.log(error);
      });
    },
    async setOwnMediaStream(context, mediaStream) {
      if (context.state.instructorRole) {
        context.commit('setInstructorMediaStream', mediaStream);
      } else {
        context.commit('setCustomerMediaStream', mediaStream);
      }
    },
    async closeMediaStream(context) {
      if (context.getters.ownMediaStream !== null) {
        context.getters.ownMediaStream.getTracks().forEach((track) => {
          track.stop();
        });
        context.dispatch('setOwnMediaStream', null);
      }
      if (context.state.peerMediaConnection !== null) {
        context.state.peerMediaConnection.stop();
        context.commit('setPeerMediaConnection', null);
      }
    },
    async subscribeCustomerToCall(context) {
      console.log('subscribe customer to a call');
      context.state.peer.on('call', (call) => {
        call.answer(context.state.customerMediaStream);
        context.commit('setPeerMediaConnection', call);
        call.on('stream', (stream) => {
          console.log('instructor stream received');
          context.commit('setPeerStreamConnected', true);
          context.commit('setInstructorMediaStream', stream);
        });
        call.on('close', () => {
          context.commit('setPeerStreamConnected', false);
          context.commit('setInstructorMediaStream', null);
        });
      });
    },
    async publishCustomerPeer(context) {
      client.get('personal-training/publish-customer-peer', {
        params: {
          trainingId: context.state.personalTrainigId,
          peerId: context.state.customerPeerId,
        },
      });
    },
    async loadCustomerPeer(context) {
      client.get('personal-training/load-customer-peer', {
        params: {
          trainingId: context.state.personalTrainigId,
        },
      }).then((response) => {
        console.log(response, response.data);

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
      dataConnection.on('open', () => {
        context.commit('setPeerDataConnected', true);
        context.commit('setPeerDataConnection', dataConnection);
      });
      dataConnection.on('data', (data) => {
        context.dispatch('receivePeerData', data);
      });
      dataConnection.on('close', () => {
        context.commit('setPeerDataConnected', false);
        context.commit('setPeerDataConnection', null);
      });
    },
    async callCustomer(context) {
      console.log('call customer');
      const call = context.state.peer.call(
        context.state.customerPeerId,
        context.state.instructorMediaStream,
      );
      call.on('stream', (stream) => {
        console.log('client stream received');
        context.commit('setPeerStreamConnected', true);
        context.commit('setCustomerMediaStream', stream);
      });
      call.on('close', () => {
        context.commit('setPeerStreamConnected', false);
        context.commit('setCustomerMediaStream', null);
      });
    },
    async receivePeerData(context, data) {
      // @TODO imlement chat message save
      console.log('Received data:', data);
    },
  },
  mutations: {
    addChatMessage(state, message) {
      state.chatSession.push(message);
    },
    setVideoSessionStatus(state, value) {
      state.videoSessionStatus = value;
    },
    setMicEnabled(state, value) {
      state.micEnabled = value;
    },
    setCameraEnabled(state, value) {
      state.cameraEnabled = value;
    },
    setView(state, value) {
      state.view = value;
    },
    setFullScreenMode(state, value) {
      state.fullScreenMode = value;
    },
    showChatModal(state, value) {
      state.showChatModal = value;
    },
    showJoinOptionsModal(state, value) {
      state.showJoinOptionsModal = value;
    },
    showLeaveMeetingModal(state, value) {
      state.showLeaveMeetingModal = value;
    },
    showViewOptionsModal(state, value) {
      state.showViewOptionsModal = value;
    },
    setPeer(state, value) {
      state.peer = value;
    },
    setCustomerPeerId(state, peerId) {
      state.customerPeerId = peerId;
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
    setInstructorRole(state, value) {
      state.instructorRole = value;
    },
    setInstructorMediaStream(state, value) {
      state.instructorMediaStream = value;
    },
    setCustomerMediaStream(state, value) {
      state.customerMediaStream = value;
    },
    setPersonalTrainingId(state, value) {
      state.personalTrainigId = value;
    },
  },
  getters: {
    chatSession: (state) => state.chatSession,
    view: (state) => state.view,
    isJoinedVideoSession: (state) => state.videoSessionStatus,
    isMicEnabled: (state) => state.micEnabled,
    isCameraEnabled: (state) => state.cameraEnabled,
    isShowJoinOptionsModal: (state) => state.showJoinOptionsModal,
    isShowLeaveMeetingModal: (state) => state.showLeaveMeetingModal,
    isShowViewOptionsModal: (state) => state.showViewOptionsModal,
    isShowChatModal: (state) => state.showChatModal,
    peer: (state) => state.peer,
    isInstructorRole: (state) => state.instructorRole,
    ownMediaStream: (state) => (
      state.instructorRole
        ? state.instructorMediaStream
        : state.customerMediaStream),
    customerMediaStream: (state) => state.customerMediaStream,
    instructorMediaStream: (state) => state.instructorMediaStream,
  },
};
