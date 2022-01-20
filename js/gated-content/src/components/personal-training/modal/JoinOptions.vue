<template>
  <Modal
    class="modal-join-options"
    :style="{'display': isShowJoinOptionsModal ? 'table' : 'none'}"
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
        <AudioIndicator
          v-if="localMediaStream"
          :media-stream="localMediaStream"
          :mic-enabled="isMicEnabled"
        />
        <VideoIndicator v-if="localMediaStream" :cam-enabled="isCameraEnabled" />
        <video
          v-if="localMediaStream && isCameraEnabled"
          :srcObject.prop="localMediaStream"
          :volume.prop="0"
          autoplay="autoplay"
          playsinline
          muted="muted"
        ></video>
      </div>
      <div class="controls">
        <div class="mic"
             :class="{enabled: localMediaStream && isMicEnabled}"
             @click="toggleMicEnabled"
        >
          <div>
            <SvgIcon :icon="micIcon" class="fill-white"></SvgIcon>
          </div>
          <span>Microphone</span>
        </div>
        <div class="cam"
             :class="{enabled: localMediaStream && isCameraEnabled}"
             @click="toggleCameraEnabled"
        >
          <div>
            <SvgIcon :icon="cameraIcon" class="fill-white"></SvgIcon>
          </div>
          <span>Video Camera</span>
        </div>
        <div class="settings"
             @click="toggleDeviceManagerModal"
        >
          <div>
            <SvgIcon icon="settings_black_24dp"
                     class="fill-white"></SvgIcon>
          </div>
          <span>Settings</span>
        </div>
      </div>
      <div class="join">
        <div
          v-if="!localMediaStream"
          class="text-center error-message"
        >
          Please, share your camera and mic, and click below
        </div>
        <div
          v-if="localMediaStream"
          class="indigo-button cachet-book-20 text-white"
          @click="joinVideoSession"
        >Join meeting
        </div>
        <div
          v-else
          class="indigo-button cachet-book-20 disabled"
        >Join meeting</div>
      </div>
    </template>
  </Modal>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import AudioIndicator from '@/components/personal-training/meeting/AudioIndicator.vue';
import VideoIndicator from '@/components/personal-training/meeting/VideoIndicator.vue';
import Modal from '@/components/modal/Modal.vue';
import SvgIcon from '@/components/SvgIcon.vue';

export default {
  components: {
    AudioIndicator, VideoIndicator, SvgIcon, Modal,
  },
  computed: {
    ...mapGetters([
      'isShowJoinOptionsModal',
      'isMicEnabled',
      'isCameraEnabled',
      'localMediaStream',
    ]),
    micIcon() {
      return this.localMediaStream && this.isMicEnabled ? 'mic_black_24dp' : 'mic_off_black_24dp';
    },
    cameraIcon() {
      return this.localMediaStream && this.isCameraEnabled ? 'videocam_black_24dp' : 'videocam_off_black_24dp';
    },
  },
  methods: {
    ...mapActions([
      'toggleCameraEnabled',
      'toggleMicEnabled',
      'toggleShowJoinOptionsModal',
      'toggleDeviceManagerModal',
      'joinVideoSession',
    ]),
  },
};
</script>
