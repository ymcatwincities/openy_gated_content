<template>
  <div class="audio-state-indicator">
    <SvgIcon
      v-if="mediaStream && !micEnabled"
      icon="mic_off_black_24dp"
      class="fill-white"></SvgIcon>
    <div v-if="micEnabled" class="indication" :style="{width: width + '%'}"></div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import SvgIcon from '@/components/SvgIcon.vue';

export default {
  name: 'AudioIndicator',
  components: { SvgIcon },
  props: {
    mediaStream: {
      type: Object,
      required: true,
    },
    micEnabled: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      intervalId: 0,
      localAnalyser: null,
      mediaStreamSource: null,
      gainNode: null,
      width: 20,
    };
  },
  computed: {
    ...mapGetters([
      'audioContext',
    ]),
  },
  watch: {
    mediaStream(newValue) {
      if (newValue === null) {
        this.mediaStreamSource = null;
      }
    },
  },
  mounted() {
    this.intervalId = setInterval(() => {
      if (this.localAnalyser === null) {
        this.localAnalyser = this.audioContext.createAnalyser();
        this.localAnalyser.smoothingTimeConstant = 0.1;
        this.localAnalyser.fftSize = 32;

        this.gainNode = this.audioContext.createGain();
        this.gainNode.gain.value = 0;

        this.localAnalyser.connect(this.gainNode);
        this.gainNode.connect(this.audioContext.destination);
      }
      if (this.mediaStreamSource === null && this.mediaStream !== null) {
        this.mediaStreamSource = this.audioContext.createMediaStreamSource(this.mediaStream);
        this.mediaStreamSource.connect(this.localAnalyser);
      }
      const bFrequencyData = new Uint8Array(this.localAnalyser.frequencyBinCount);
      this.localAnalyser.getByteFrequencyData(bFrequencyData);

      let values = 0;
      const { length } = bFrequencyData;
      for (let i = 0; i < length; i += 1) {
        values += bFrequencyData[i];
      }

      this.width = 20 + 80 * ((values / length) / 256);
    }, 100);
  },
  beforeDestroy() {
    clearInterval(this.intervalId);
  },
};
</script>
