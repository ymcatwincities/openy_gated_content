<template>
  <div class="meeting-player" :class="view">
    <video
      :key="partnerMediaStream === null"
      class="partner"
      :srcObject.prop="partnerMediaStream ? partnerMediaStream : ''"
      autoplay="autoplay"
      playsinline
      :class="{
        connected: partnerMediaStream !== null,
        'video-disabled': !remoteVideoState,
      }"
    ></video>
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

export default {
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
      'remoteVideoState',
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
