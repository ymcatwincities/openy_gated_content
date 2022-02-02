<template>
  <div class="control-panel text-white">
    <div class="chat"
         :class="{'unread': unreadLiveChatMessagesCount}"
         @click="openModal"
    >
      <SvgIcon icon="question_answer_black_24dp"></SvgIcon>
      <span v-if="unreadLiveChatMessagesCount" class="unread-count">
        {{ unreadLiveChatMessagesCount }}
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
    ]),
  },
  methods: {
    ...mapActions([
      'toggleShowLiveChatUserNameModal',
      'toggleShowLiveChatModal',
    ]),
    async openModal() {
      if (this.isOpenLiveChatNameModal) {
        this.toggleShowLiveChatModal();
      } else {
        this.toggleShowLiveChatUserNameModal(false);
      }
    },
  },
};
</script>
