<template>
  <Modal
    class="modal-join-options"
    :class="{'d-none': !isShowJoinOptionsModal}"
  >
    <template #body>
      <div class="header cachet-book-18">
        Join Options
        <button @click="toggleShowJoinOptionsModal">
          &times;
        </button>
      </div>
      <div class="video"
        :style="{background: localMediaStream && isCameraEnabled ? 'none':''}"
      >
        <video
          v-if="localMediaStream && isCameraEnabled"
          :srcObject.prop="localMediaStream"
          :volume.prop="0"
          autoplay="autoplay"
          muted="muted"
        ></video>
      </div>
      <div class="controls">
        <div class="mic"
             :class="{enabled: isMicEnabled}"
             @click="toggleMicEnabled"
        >
          <div>
            <SvgIcon :icon="isMicEnabled ? 'mic_black_24dp':'mic_off_black_24dp'"
                     class="fill-white mic-icon"></SvgIcon>
            <!--SvgIcon :icon="isMicEnabled ? 'toggle_on_black_24dp':'toggle_off_black_24dp'"
                     class="fill-white switch-icon"
                     :class="isMicEnabled ? 'fill-camarone':''"></SvgIcon-->
          </div>
          <span>Microphone</span>
        </div>
        <div class="cam"
             :class="{enabled: isCameraEnabled}"
             @click="toggleCameraEnabled"
        >
          <div>
            <SvgIcon :icon="isCameraEnabled ? 'videocam_black_24dp':'videocam_off_black_24dp'"
                     class="fill-white camera-icon"></SvgIcon>
            <!--SvgIcon :icon="isCameraEnabled ? 'toggle_on_black_24dp':'toggle_off_black_24dp'"
                     class="fill-white switch-icon"
                     :class="isCameraEnabled ? 'fill-camarone':''"></SvgIcon-->
          </div>
          <span>Video Camera</span>
        </div>
      </div>
      <div class="join">
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
      'localMediaStream',
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
