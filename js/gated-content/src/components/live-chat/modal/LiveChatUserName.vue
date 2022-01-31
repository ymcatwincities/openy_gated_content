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
          v-model="liveChatLocalName">
        <span v-if="error" class="text-red verdana-14-12">{{ error }}</span>
      </div>
      <button
        @click="submit"
        class="gc-button"
        :disabled="!liveChatLocalName">
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
      liveChatLocalName: '',
    };
  },
  computed: {
    ...mapGetters([
      'isShowLiveChatUserNameModal',
    ]),
  },
  created() {
    client
      .get('livechat/get-user-name')
      .then((response) => {
        this.liveChatLocalName = response.data.liveStreamName;
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
        .dispatch('updateLiveChatLocalName', this.liveChatLocalName)
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
