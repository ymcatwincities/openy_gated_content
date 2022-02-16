<template>
  <div class="control-panel">
    <div class="chat"
         :class="{'unread': unreadLiveChatMessagesCount}"
         @click="openModal"
    >
      <SvgIcon icon="question_answer_black_24dp"></SvgIcon>
      <span v-if="unreadLiveChatMessagesCount" class="unread-count">
        {{ unreadLiveChatMessagesCount }}
      </span>
      <span class="restart-chat">
        Restart Livechat
      </span>
    </div>
    <LiveChatUserName></LiveChatUserName>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import SvgIcon from '@/components/SvgIcon.vue';
import LiveChatUserName from '@/components/live-chat/modal/LiveChatUserName.vue';

export default {
  components: { SvgIcon, LiveChatUserName },
  computed: {
    ...mapGetters([
      'unreadLiveChatMessagesCount',
      'isOpenLiveChatNameModal',
      'liveChatMeetingId',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleShowLiveChatUserNameModal',
      'toggleShowLiveChatModal',
      'restartLiveChat',
      'restartLivechatRequest',
      'setIsDisabledLivechat',
      'sendLiveChatTechMessage',
    ]),
    async openModal() {
      const message = {
        enableChat: 1,
      };
      this.sendLiveChatTechMessage(message);
      this.$store.commit('setIsDisabledLivechat', false);
      if (this.isOpenLiveChatNameModal) {
        this.toggleShowLiveChatModal();
      } else {
        this.toggleShowLiveChatUserNameModal(false);
      }
    },
  },
};
</script>
