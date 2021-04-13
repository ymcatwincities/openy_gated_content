<template>
  <div class="control-panel bg-black text-white">
    <div class="mic"
         :class="{enabled: isMicEnabled}"
         @click="toggleMicEnabled"
    >
      <SvgIcon :icon="isMicEnabled ? 'mic_black_24dp':'mic_off_black_24dp'"
               class="fill-white"></SvgIcon>
      <span>Mic</span>
    </div>
    <div class="cam"
         :class="{enabled: isCameraEnabled}"
         @click="toggleCameraEnabled"
    >
      <SvgIcon :icon="isCameraEnabled ? 'videocam_black_24dp':'videocam_off_black_24dp'"
               class="fill-white"></SvgIcon>
      <span>Camera</span>
    </div>
    <div class="view"
         :class="view"
         @click="toggleViewOptionsModal"
    >
      <SvgIcon icon="view-horizontal-split-gray" class="fill-white"></SvgIcon>
      <span>View</span>
    </div>
    <div class="leave"
         @click="toggleShowLeaveMeetingModal"
    >
      <SvgIcon icon="cancel_black_24dp" class="fill-white"></SvgIcon>
      <span>Leave</span>
    </div>
    <div class="chat ml-auto"
         @click="toggleShowChatModal"
    >
      <SvgIcon icon="question_answer_black_24dp" class="fill-white"></SvgIcon>
      <span>Chat</span>
    </div>
    <div class="full-screen"
         @click="toggleFullScreenMode"
    >
      <SvgIcon icon="fullscreen_black_24dp" class="fill-white"></SvgIcon>
      <span>Full</span>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import SvgIcon from '@/components/SvgIcon.vue';

export default {
  components: { SvgIcon },
  computed: {
    ...mapGetters([
      'isMicEnabled',
      'isCameraEnabled',
      'view',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleCameraEnabled',
      'toggleMicEnabled',
      'toggleViewOptionsModal',
      'toggleShowChatModal',
      'toggleShowLeaveMeetingModal',
      'toggleFullScreenMode',
    ]),
  },
};
</script>
