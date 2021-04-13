<template>
  <Modal
    class="modal-join-options"
    :class="{'d-none': !isShowJoinOptionsModal}"
  >
    <template #body>
      <div class="video">
        <video
          v-if="ownMediaStream"
          :srcObject.prop="ownMediaStream"
          autoplay="autoplay"
        ></video>
      </div>
      <div class="header cachet-book-18">
        Join Options
        <button @click="toggleShowJoinOptionsModal">
          &times;
        </button>
      </div>
      <div class="controls">
        <div class="mic"
             :class="{enabled: isMicEnabled}"
             @click="toggleMicEnabled"
        >
          <SvgIcon icon="sliders-h-solid" class="fill-white"></SvgIcon>
          <span>Microphone</span>
        </div>
        <div class="cam"
             :class="{enabled: isCameraEnabled}"
             @click="toggleCameraEnabled"
        >
          <SvgIcon icon="sliders-h-solid" class="fill-white"></SvgIcon>
          <span>Video Camera</span>
        </div>
      </div>
      <div>
        <div
          class="indigo-button cachet-book-20 text-white"
          @click="joinVideoSession"
        >Join meeting
        </div>
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
      'isShowJoinOptionsModal',
      'isMicEnabled',
      'isCameraEnabled',
      'ownMediaStream',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleCameraEnabled',
      'toggleMicEnabled',
      'toggleShowJoinOptionsModal',
      'joinVideoSession',
    ]),
  },
};
</script>
