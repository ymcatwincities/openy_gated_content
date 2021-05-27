<template>
  <div :class="media.field_media_source">
    <VueVideoWrapper
      ref="player"
      :player="player"
      :videoId="videoId"
      :options="{responsive: 'true', url: media.field_media_video_embed_field}"
      :player-vars="handleAttributes()"
      @loaded="$refs.player.pause()"
      @play="handlePlay()"
      @pause="handlePause()"
      @ended="handleEnded()"
    />
  </div>
</template>

<script>
import VueVideoWrapper from 'vue-video-wrapper';

export default {
  name: 'MediaPlayer',
  data() {
    return {
      playbackLogged: false,
      playbackInProgress: false,
      intervalId: 0,
    };
  },
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
  },
  computed: {
    player() {
      return this.media.field_media_source.startsWith('youtube') ? 'youtube' : 'vimeo';
    },
    videoId() {
      let embedObj = this.media.field_media_video_embed_field;
      if (this.media.field_media_source === 'youtube_playlist') {
        embedObj = embedObj.match(/(\?|&)v=([^&#]+)/).pop();
        return embedObj;
      }
      // If the video matches Vimeo's private link format, return the full url.
      if (embedObj.match(/^https?:\/\/(www\.)?vimeo.com\/([0-9]*)(\/[a-zA-Z0-9]+)$/)) {
        return embedObj;
      }
      return this.media.field_media_video_id;
    },
  },
  methods: {
    reload() {
      this.$forceUpdate();
    },
    handlePlayerEvent(eventType) {
      this.$emit('playerEvent', eventType);
    },
    handlePlay() {
      this.playbackInProgress = true;
      if (this.playbackLogged) {
        return;
      }
      this.playbackLogged = true;
      this.handlePlayerEvent('videoPlaybackStarted');
    },
    handleAttributes() {
      if (this.media.field_media_source === 'youtube') {
        return {
          rel: 0,
        };
      }
      return false;
    },
    handlePause() {
      this.playbackInProgress = false;
    },
    handleEnded() {
      this.playbackInProgress = false;
      this.handlePlayerEvent('videoPlaybackEnded');
    },
  },
  mounted() {
    this.intervalId = setInterval(() => {
      if (this.playbackInProgress) {
        this.$log.trackActivity({ path: this.$route.fullPath });
      }
    }, 60 * 1000);
  },
  updated() {
    this.playbackLogged = false;
    this.playbackInProgress = false;
  },
  beforeDestroy() {
    clearInterval(this.intervalId);
  },
};
</script>
