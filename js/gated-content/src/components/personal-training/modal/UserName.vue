<template>
  <Modal
    class="modal-leave-meeting text-black"
    :style="{'display': isShowUserNameModal ? 'table' : 'none'}"
    @close="toggleShowUserNameModal"
  >
    <template #header>
      <div class="header-info">
        <span>Specify your name</span>
      </div>
    </template>
    <template #body>
      <div class="select">
        <input
          type="text"
          class="form-control w-100"
          id="meetingUserNameInput"
          v-model="name"
        >
        <span v-if="error" class="text-red verdana-14-12">{{ error }}</span>
      </div>
      <button
        @click="submit"
        class="gc-button"
        :disabled="!name"
      >
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
      name: '',
    };
  },
  computed: {
    ...mapGetters([
      'isShowUserNameModal',
    ]),
  },
  created() {
    client
      .get('personal-training/get-user-name')
      .then((response) => {
        this.name = response.data.name;
      });
  },
  methods: {
    ...mapActions([
      'toggleShowUserNameModal',
      'toggleShowJoinOptionsModal',
      'updateLocalName',
    ]),
    async submit() {
      this.error = '';
      await this.$store
        .dispatch('updateLocalName', this.name)
        .then(() => {
          this.toggleShowUserNameModal();
          this.toggleShowJoinOptionsModal();
        })
        .catch((error) => {
          this.error = error.response.data.message;
        });
    },
  },
};
</script>
