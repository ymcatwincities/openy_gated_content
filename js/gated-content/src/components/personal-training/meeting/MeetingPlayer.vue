<template>
  <div class="meeting-player" :class="view">
    <div class="video-wrapper partner" :class="{connected: partnerMediaStream !== null}">
      <AudioIndicator
        v-if="partnerMediaStream"
        :media-stream="partnerMediaStream"
        :mic-enabled="partnerMicEnabled"
      />
      <VideoIndicator v-if="partnerMediaStream" :cam-enabled="partnerCamEnabled" />
      <video
        :key="partnerMediaStream === null"
        :srcObject.prop="partnerMediaStream ? partnerMediaStream : ''"
        autoplay="autoplay"
        playsinline
      >
      </video>
    </div>
    <div class="video-wrapper local" :class="{connected: localMediaStream !== null}">
      <AudioIndicator
        v-if="localMediaStream"
        :media-stream="localMediaStream"
        :mic-enabled="isMicEnabled"
      />
      <VideoIndicator v-if="localMediaStream" :cam-enabled="isCameraEnabled" />
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
import AudioIndicator from '@/components/personal-training/meeting/AudioIndicator.vue';
import VideoIndicator from '@/components/personal-training/meeting/VideoIndicator.vue';

export default {
  components: { AudioIndicator, VideoIndicator },
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
