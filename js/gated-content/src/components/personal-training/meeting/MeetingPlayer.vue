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
    <div class="video-wrapper partner" :class="{connected: partnerMediaStream !== null}">
      <video
        :key="partnerMediaStream === null"
        :srcObject.prop="partnerMediaStream ? partnerMediaStream : ''"
        autoplay="autoplay"
        playsinline
      >
      </video>
    </div>
    <div class="video-wrapper local" :class="{connected: localMediaStream !== null}">
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
