<template>
  <div :class="media.field_media_source">
    <VueVideoWrapper
      :player="player"
      :videoId="media.field_media_video_id"
      :options="{responsive: 'true'}"
      @play="handlePlay('videoPlaybackStarted')"
      @pause="handlePlay('videoPlaybackPaused')"
      @ended="handlePlay('videoPlaybackEnded')"
    />
  </div>
</template>

<script>
import VueVideoWrapper from 'vue-video-wrapper';

export default {
  name: 'MediaPlayer',
  components: {
    VueVideoWrapper,
  },
  props: {
    media: {
      type: Object,
      required: true,
    },
  },
  watch: {
    media: 'reload',
    source: 'reload',
  },
  computed: {
    player() {
      return this.media.field_media_source === 'youtube' ? 'youtube' : 'vimeo';
    },
  },
  methods: {
    reload() {
      this.$forceUpdate();
    },
    handlePlay(eventType) {
      this.$emit('playerEvent', eventType);
    },
  },
};
</script>
