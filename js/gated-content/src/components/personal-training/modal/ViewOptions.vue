<template>
  <Modal
    class="modal-view-options text-black"
    :style="{'display': isShowViewOptionsModal ? 'table' : 'none'}"
    @close="toggleViewOptionsModal"
  >
    <template #header><div class="cachet-book-18">View Options</div></template>
    <template #body>
      <div class="verdana-14-12"
        :class="{selected: view === 'view-inset'}"
        @click="setInsetView"
      >
        <SvgIcon icon="view-inset" class="fill-white"></SvgIcon>
        Inset View
      </div>
      <div class="verdana-14-12"
        :class="{selected: view === 'view-vertical'}"
        @click="setVerticalView"
      >
        <SvgIcon icon="view-vertical" class="fill-white"></SvgIcon>
        Vertical Split View
      </div>
      <div class="verdana-14-12"
        :class="{selected: view === 'view-horizontal'}"
        @click="setHorizontalView"
      >
        <SvgIcon icon="view-horizontal" class="fill-white"></SvgIcon>
        Horizontal Split View
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
      'view',
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
