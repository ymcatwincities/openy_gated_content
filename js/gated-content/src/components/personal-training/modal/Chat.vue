<template>
  <Modal
    class="modal-chat text-black"
    :class="{'d-none': !isShowChatModal}"
    @close="toggleShowChatModal"
  >
    <template #header>Chat</template>
    <template #body>
      <div
        v-for="msg in chatSession"
        :key="msg.message"
      >
        <div>{{ msg.author }}</div>
        <div>{{ formatDate(msg.date) }}</div>
        <div>{{ msg.message }}</div>
      </div>

      <input
        type="text"
        placeholder="Message"
        v-model.trim="newMessage"
        @keyup.enter="messageEnterEvent(newMessage)"
      />
      <button
        @click="messageEnterEvent(newMessage)"
      >Send message</button>
    </template>
  </Modal>
</template>

<script>
import Modal from '@/components/modal/Modal.vue';
import { mapGetters, mapActions } from 'vuex';
import dayjs from 'dayjs';

export default {
  components: { Modal },
  data() {
    return {
      newMessage: '',
    };
  },
  computed: {
    ...mapGetters([
      'isShowChatModal',
      'chatSession',
    ]),
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
  },
};
</script>
