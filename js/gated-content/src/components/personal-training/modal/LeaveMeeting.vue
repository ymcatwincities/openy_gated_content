<template>
  <Modal
    class="modal-leave-meeting text-black"
    :class="{'d-none': !isShowLeaveMeetingModal}"
    @close="toggleShowLeaveMeetingModal"
  >
    <template #header>Leave meeting?</template>
    <template #body>
      <div
        class="button"
        @click="toggleShowLeaveMeetingModal"
      >No</div>
      <div
        class="button"
        @click="leaveVideoSession"
      >Yes</div>
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
      'isShowLeaveMeetingModal',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleShowLeaveMeetingModal',
      'leaveVideoSession',
    ]),
  },
};
</script>
