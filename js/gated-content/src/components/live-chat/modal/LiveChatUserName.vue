<template>
  <Modal
    class="modal-leave-meeting text-black"
    :style="{'display': isShowLiveChatUserNameModal ? 'table' : 'none'}"
    @close="toggleShowLiveChatUserNameModal">

    <template #header>Specify your name</template>
    <template #body>
      <div class="select">
        <input
          type="text"
          class="form-control w-100"
          id="meetingLiveChatUserNameInput"
          v-model="liveChatName">
        <span v-if="error" class="text-red verdana-14-12">{{ error }}</span>
      </div>
      <button
        @click="submit"
        class="gc-button"
        :disabled="!liveChatName">
        Next
      </button>
    </template>

  </Modal>
</template>
<script>
import { mapGetters, mapActions } from 'vuex';
import Modal from '@/components/modal/Modal.vue';
import client from '@/client';

export default {
  components: { Modal },
  data() {
    return {
      error: null,
      liveChatName: '',
    };
  },
  computed: {
    ...mapGetters([
      'isShowLiveChatUserNameModal',
      'liveChatLocalName',
    ]),
  },
  created() {
    client
      .get('livechat/get-livechat-data')
      .then((response) => {
        this.liveChatName = response.data.name;
      });
  },
  methods: {
    ...mapActions([
      'toggleShowLiveChatModal',
      'toggleShowLiveChatUserNameModal',
      'updateLiveChatLocalName',
    ]),
    async submit() {
      this.error = '';
      await this.$store
        .dispatch('updateLiveChatLocalName', this.liveChatName)
        .then(() => {
          this.toggleShowLiveChatUserNameModal();
          this.toggleShowLiveChatModal();
        })
        .catch((error) => {
          this.error = error.response.data.message;
        });
    },
  },
};
</script>
