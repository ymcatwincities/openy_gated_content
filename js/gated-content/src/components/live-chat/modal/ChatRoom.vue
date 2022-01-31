<template>
  <Modal
    class="modal-chat text-black"
    :style="{'display': isShowLiveChatModal ? 'table' : 'none'}"
    @close="toggleShowLiveChatModal"
  >
    <template #header><span>Chat</span></template>
    <template #body>
      <div
        v-for="(msg, index) in liveChatSession"
        :key="index"
        class="message"
        :class="{'d-right': msg.author === liveChatLocalName, 'd-left': msg.author !== liveChatLocalName}"
      >
        <div class="user-icon">
          <span>{{ getMsgAuthor(msg.author, true) }}</span>
        </div>
        <div class="message-card">
          <div class="message-header">
            <h4 class="message-author mb-0">{{ getMsgAuthor(msg.author) }}</h4>
            <div class="message-time">{{ formatDate(msg.date) }}</div>
          </div>
          <div class="message-body">{{ msg.message }}</div>
        </div>
      </div>
    </template>
    <template #footer>
      <input
        type="text"
        placeholder="Message"
        v-model.trim="newMessage"
        @keyup.enter="messageEnterEvent(newMessage)"
      />
      <button
        @click="messageEnterEvent(newMessage)"
        :disabled="newMessage.length === 0"
      >
        <SendIcon :color="'white'"></SendIcon>
      </button>
    </template>
  </Modal>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import Modal from '@/components/modal/Modal.vue';
import SendIcon from '@/components/svg/SendIcon.vue';

export default {
  components: { Modal, SendIcon },
  data() {
    return {
      newMessage: '',
    };
  },
  computed: {
    ...mapGetters([
      'isShowLiveChatModal',
      'liveChatSession',
      'unreadLiveChatMessagesCount',
      'liveChatLocalName',
      'getAppSettings',
    ]),
  },
  watch: {
    unreadLiveChatMessagesCount: 'beep',
  },
  methods: {
    ...mapActions([
      'toggleShowLiveChatModal',
      'sendLiveChatMessage',
      'initRatchetServer',
    ]),
    messageEnterEvent(message) {
      this.newMessage = '';
      this.sendLiveChatMessage(message);
    },
    formatDate(date) {
      return this.$dayjs.date(date).format('ddd, MMM D, YYYY @ h:mm a');
    },
    getMsgAuthor(author, short = false) {
      if (short) {
        return author.slice(0, 1);
      }
      return author === this.liveChatLocalName ? 'Me' : author;
    },
  },
  mounted() {
    this.initRatchetServer();
  },
};
</script>
