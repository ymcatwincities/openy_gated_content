export default {
  state: {
    ratchetServer: null,
    ratchetServerConnected: false,
  },
  actions: {
    async initRatchetServer(context) {
      const { liveChatMeetingId } = context.getters;
      const { port, mode } = context.getters.liveChatRatchetConfigs;

      const protocol = mode === 'https' ? 'wss://' : 'ws://';
      const serverURL = `${window.location.host}:${port}`;

      const ws = new WebSocket(`${protocol}${serverURL}/${liveChatMeetingId}`);

      ws.onopen = () => {
        context.commit('clearLiveChatMessage');
        context.commit('setRatchetServerConnected', liveChatMeetingId);
      };

      ws.onmessage = (event) => {
        const data = JSON.parse(event.data);

        if (data.count) {
          context.dispatch('updateOnlineClientCount', data.count);
        }
        if (data.message_type === 'disableChat') {
          context.commit('setIsDisabledLivechat', true);
          context.commit('clearLiveChatMessage');
        } else if (data.message_type === 'enableChat') {
          context.commit('setIsDisabledLivechat', false);
        } else if (data.message_type === 'history') {
          // eslint-disable-next-line camelcase
          const { history, is_chat_disabled } = data;

          // eslint-disable-next-line camelcase
          if (is_chat_disabled) {
            context.commit('setIsDisabledLivechat', true);
            context.commit('clearLiveChatMessage');
          }

          if (!context.getters.liveChatSession.length) {
            // eslint-disable-next-line array-callback-return
            history.map((value) => {
              const chatRoomMsg = {
                author: value.username,
                uid: value.uid,
                message: value.message,
                // eslint-disable-next-line radix
                date: parseInt(value.created),
              };

              context.commit('addLiveChatMessage', chatRoomMsg);
            });
          }
        } else {
          const chatRoomMsg = {
            author: data.username,
            uid: data.uid,
            message: data.message,
            date: new Date(),
          };

          context.dispatch('receiveChatMessage', chatRoomMsg);
        }
      };

      ws.onclose = () => {
        context.commit('setRatchetServerConnected', false);
        // eslint-disable-next-line no-undef
        _.delay(() => {
          if (!context.getters.ratchetServer) {
            context.dispatch('initRatchetServer');
          }
        }, 1000);
      };

      ws.onerror = () => {
        context.commit('setRatchetServerConnected', false);
      };

      context.commit('setRatchetServer', ws);
    },
    async sendLiveChatData(context, message) {
      if (context.getters.ratchetServerConnected) {
        context.getters.ratchetServer.send(JSON.stringify(message));
      }
    },
  },
  mutations: {
    setRatchetServer(state, value) {
      state.ratchetServer = value;
    },
    setRatchetServerConnected(state, value) {
      state.ratchetServerConnected = value;
    },
  },
  getters: {
    ratchetServer: (state) => state.ratchetServer,
    ratchetServerConnected: (state) => state.ratchetServerConnected,
  },
};
