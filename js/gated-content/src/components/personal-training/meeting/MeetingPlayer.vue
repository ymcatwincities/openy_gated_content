<template>
  <div class="meeting-player" :class="view">
    <div class="video-wrapper partner" :class="{connected: partnerMediaStream !== null}">
      <AudioIndicator
        v-if="partnerMediaStream"
        :media-stream="partnerMediaStream"
        :mic-enabled="partnerMicEnabled"
      />
      <div class="video-state-indicator">
        <SvgIcon
          v-if="partnerMediaStream && !partnerCamEnabled"
          icon="videocam_off_black_24dp"
          class="fill-white"></SvgIcon>
      </div>
      <video
        :key="partnerMediaStream === null"
        :srcObject.prop="partnerMediaStream ? partnerMediaStream : ''"
        autoplay="autoplay"
        playsinline
      >
      </video>
    </div>
    <div class="video-wrapper local" :class="{connected: localMediaStream !== null}">
      <AudioIndicator :media-stream="localMediaStream" :mic-enabled="isMicEnabled" />
      <div class="video-state-indicator">
        <SvgIcon
          v-if="localMediaStream && !isCameraEnabled"
          icon="videocam_off_black_24dp"
          class="fill-white"></SvgIcon>
      </div>
      <video
        :key="localMediaStream === null"
        :srcObject.prop="localMediaStream ? localMediaStream : ''"
        autoplay="autoplay"
        muted="muted"
        playsinline
        :volume.prop="0"
      ></video>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import SvgIcon from '@/components/SvgIcon.vue';
import AudioIndicator from '@/components/personal-training/meeting/AudioIndicator.vue';

export default {
  components: { AudioIndicator, SvgIcon },
  data() {
    return {
      intervalId: 0,
    };
  },
  computed: {
    ...mapGetters([
      'view',
      'isMicEnabled',
      'isCameraEnabled',
      'localMediaStream',
      'partnerMediaStream',
      'partnerCamEnabled',
      'partnerMicEnabled',
    ]),
  },
  mounted() {
    this.intervalId = setInterval(() => {
      if (this.partnerMediaStream) {
        this.$log.trackActivity({ path: this.$route.fullPath });
      }
    }, 60 * 1000);
  },
  beforeDestroy() {
    clearInterval(this.intervalId);
  },
};
</script>
