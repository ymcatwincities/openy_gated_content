<template>
  <Modal
    class="modal-chat text-black"
    :class="{'d-none': !isShowChatModal}"
    @close="toggleShowChatModal"
  >
    <template #header><span>Chat</span></template>
    <template #body>
      <div
        v-for="(msg, index) in chatSession"
        :key="index"
        class="message"
        :class="{'d-right': msg.author === localName, 'd-left': msg.author !== localName}"
      >
        <div class="user-icon">
          <span>{{ msg.author === localName ? 'Me' : msg.author.charAt(0).toUpperCase() }}</span>
        </div>
        <div class="message-card">
          <div class="message-header">
            <h4 class="message-author mb-0">{{ msg.author }}</h4>
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
import Modal from '@/components/modal/Modal.vue';
import SendIcon from '@/components/svg/SendIcon.vue';
import { mapGetters, mapActions } from 'vuex';
import dayjs from 'dayjs';

export default {
  components: { Modal, SendIcon },
  data() {
    return {
      newMessage: '',
    };
  },
  computed: {
    ...mapGetters([
      'isShowChatModal',
      'chatSession',
      'unreadMessagesCount',
      'localName',
      'getAppSettings',
    ]),
  },
  watch: {
    unreadMessagesCount: 'beep',
  },
  methods: {
    ...mapActions([
      'toggleShowChatModal',
      'sendChatMessage',
    ]),
    messageEnterEvent(message) {
      this.newMessage = '';
      this.sendChatMessage(message);
    },
    formatDate(date) {
      return dayjs(date).format('ddd, MMM D, YYYY @ h:mm a');
    },
    beep() {
      if (this.unreadMessagesCount !== 0 && this.getAppSettings.newMessageSound) {
        const snd = new Audio(this.getAppSettings.newMessageSound);
        snd.play();
      }
    },
  },
};
</script>
