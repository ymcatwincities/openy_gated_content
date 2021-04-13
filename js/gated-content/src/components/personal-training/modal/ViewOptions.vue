<template>
  <Modal
    class="modal-view-options text-black"
    :class="{'d-none': !isShowViewOptionsModal}"
    @close="toggleViewOptionsModal"
  >
    <template #header>View Options</template>
    <template #body>
      <div
        @click="setHorizontalView"
      >
        <SvgIcon icon="sliders-h-solid"></SvgIcon>
        horizontal
      </div>
      <div
        @click="setVerticalView"
      >
        <SvgIcon icon="sliders-h-solid"></SvgIcon>
        vertical
      </div>
      <div
        @click="setInsetView"
      >
        <SvgIcon icon="sliders-h-solid"></SvgIcon>
        inset
      </div>
    </template>
  </Modal>
</template>

<script>
import Modal from '@/components/modal/Modal.vue';
import { mapGetters, mapActions } from 'vuex';
import SvgIcon from '@/components/SvgIcon.vue';

export default {
  components: { SvgIcon, Modal },
  computed: {
    ...mapGetters([
      'isShowViewOptionsModal',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleViewOptionsModal',
      'setHorizontalView',
      'setVerticalView',
      'setInsetView',
    ]),
  },
};
</script>
