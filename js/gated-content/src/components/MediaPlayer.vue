<template>
  <div :class="media.field_media_source">
    <VueVideoWrapper
      ref="player"
      :player="player"
      :videoId="videoId"
      :options="{responsive: 'true', url: media.field_media_video_embed_field}"
      :player-vars="handleAttributes()"
      @loaded="setTimeCode()"
      @play="handlePlay()"
      @pause="handlePause()"
      @ended="handleEnded()"
      @ready="playerReadied($event)"
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
      logStartedEventImmediately: true,
      activityIntervalId: 0,
      playbackTimeout: 0,
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
    autoplay: {
      type: Boolean,
      default: false,
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
    logPlaybackStarted() {
      if (this.playbackLogged) {
        return;
      }
      this.playbackLogged = true;
      this.handlePlayerEvent('videoPlaybackStarted');
    },
    handleLoaded() {
      if (!this.autoplay) {
        this.$refs.player.pause();
      }
    },
    handleReady() {
      if (!this.autoplay) {
        this.$refs.player.player.pauseVideo();
      }
    },
    handlePlay() {
      this.playbackInProgress = true;
      if (this.logStartedEventImmediately) {
        this.logPlaybackStarted();
      } else {
        this.playbackTimeout = setTimeout(() => this.logPlaybackStarted(), 60 * 1000);
      }
    },
    handleAttributes() {
      if (this.media.field_media_source === 'youtube') {
        return {
          rel: 0,
          autoplay: this.autoplay,
        };
      }
      return false;
    },
    handlePause() {
      this.playbackInProgress = false;
      this.logStartedEventImmediately = true;
      clearTimeout(this.playbackTimeout);
    },
    handleEnded() {
      this.playbackInProgress = false;
      this.handlePlayerEvent('videoPlaybackEnded');
    },
    playerReadied(player) {
      const timecode = this.media.field_media_video_embed_field.substring(
        this.media.field_media_video_embed_field.lastIndexOf('#t=') + 3,
        this.media.field_media_video_embed_field.lastIndexOf('s'),
      );
      if (!Number.isNaN(timecode)) {
        const timecodeInSeconds = parseInt(timecode, 10);
        player.seekTo(timecodeInSeconds, true);
        this.$refs.player.player.pauseVideo();
      }
    },
    setTimeCode() {
      if (this.player === 'vimeo') {
        const timecode = this.media.field_media_video_embed_field.substring(
          this.media.field_media_video_embed_field.lastIndexOf('#t=') + 3,
          this.media.field_media_video_embed_field.lastIndexOf('s'),
        );
        if (!Number.isNaN(timecode)) {
          const timecodeInSeconds = parseInt(timecode, 10);
          this.$refs.player.player.setCurrentTime(timecodeInSeconds);
        }
      }
      this.$refs.player.pause();
    },
  },
  mounted() {
    this.logStartedEventImmediately = !this.autoplay;
    this.activityIntervalId = setInterval(() => {
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
    clearInterval(this.activityIntervalId);
    clearTimeout(this.playbackTimeout);
  },
};
</script>
