<template>
  <div class="meeting-player" :class="view">
    <div class="partner-media-state">
      <SvgIcon
        v-if="partnerMediaStream && !partnerMicEnabled"
        icon="mic_off_black_24dp"
        class="fill-white"></SvgIcon>
      <SvgIcon
        v-if="partnerMediaStream && !partnerCamEnabled"
        icon="videocam_off_black_24dp"
        class="fill-white"></SvgIcon>
    </div>
    <video
      :key="partnerMediaStream === null"
      class="partner"
      :srcObject.prop="partnerMediaStream ? partnerMediaStream : ''"
      autoplay="autoplay"
      playsinline
      :class="{
        connected: partnerMediaStream !== null,
      }"
    >
    </video>
    <video
      :key="localMediaStream === null"
      class="local"
      :class="{connected: localMediaStream !== null ? 'connected' : ''}"
      :srcObject.prop="localMediaStream ? localMediaStream : ''"
      autoplay="autoplay"
      muted="muted"
      playsinline
      :volume.prop="0"
    ></video>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import SvgIcon from '@/components/SvgIcon.vue';

export default {
  components: { SvgIcon },
  data() {
    return {
      intervalId: 0,
    };
  },
  computed: {
    ...mapGetters([
      'view',
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
