<template>
  <Modal
    class="modal-leave-meeting text-black"
    :style="{'display': isShowUserNameModal ? 'table' : 'none'}"
    @close="toggleShowUserNameModal"
  >
    <template #header>Specify your name</template>
    <template #body>
      <div class="select">
        <input
          type="text"
          class="w-100"
          v-on:input='updateLocalName($event.target.value)'
          :value="localName"
        >
      </div>
      <button
        @click="submit"
        class="gc-button"
        :disabled="!localName"
      >
        Next
      </button>
    </template>
  </Modal>
</template>
<script>
import Modal from '@/components/modal/Modal.vue';
import { mapGetters, mapActions } from 'vuex';

export default {
  components: { Modal },
  computed: {
    ...mapGetters([
      'localName',
      'isShowUserNameModal',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleShowUserNameModal',
      'toggleShowJoinOptionsModal',
      'updateLocalName',
    ]),
    submit() {
      this.toggleShowUserNameModal();
      this.toggleShowJoinOptionsModal();
    },
  },
};
</script>
