<template>
  <Modal
    class="modal-leave-meeting text-black"
    :style="{'display': isShowLeaveMeetingModal ? 'table' : 'none'}"
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
import { mapGetters, mapActions } from 'vuex';
import Modal from '@/components/modal/Modal.vue';

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
