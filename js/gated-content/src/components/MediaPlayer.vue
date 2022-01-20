<template>
  <div :class="media.field_media_source">
    <VueVideoWrapper
      ref="player"
      :player="player"
      :videoId="videoId"
      :options="{
        responsive: 'true',
        url: mediaUrl,
      }"
      :player-vars="handleAttributes()"
      :autoplay="autoplay ? 1 : 0"
      @loaded="handleLoaded()"
      @ready="handleReady()"
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
      return this.media.field_media_source.startsWith('youtube')
        ? 'youtube'
        : 'vimeo';
    },
    videoId() {
      if (this.media.field_media_source === 'youtube_playlist') {
        return this.mediaUrl.match(/(\?|&)v=([^&#]+)/).pop();
      }
      // If the video matches Vimeo's private link format, return the full url.
      if (
        this.mediaUrl.match(
          /^https?:\/\/(www\.)?vimeo.com\/([0-9]*)(\/[a-zA-Z0-9]+)$/,
        )
      ) {
        return this.mediaUrl;
      }
      return this.media.field_media_video_id;
    },
    mediaUrl() {
      return this.media.field_media_video_embed_field;
    },
    timecode() {
      const matches = this.mediaUrl.match(/[&?#]t=((\d+)h)?((\d+)m)?(\d+)s?/);
      if (!matches) {
        return 0;
      }
      const groups = {
        hours: 2,
        minutes: 4,
        seconds: 5,
      };
      const hours = matches[groups.hours] ?? 0;
      const minutes = matches[groups.minutes] ?? 0;
      const seconds = matches[groups.seconds] ?? 0;

      return hours * 3600 + minutes * 60 + seconds;
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
      this.$refs.player.player.setCurrentTime(this.timecode);
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
        this.playbackTimeout = setTimeout(
          () => this.logPlaybackStarted(),
          60 * 1000,
        );
      }
    },
    handleAttributes() {
      let attributes = false;
      if (this.media.field_media_source === 'youtube') {
        attributes = {
          rel: 0,
          autoplay: this.autoplay ? 1 : 0,
          start: this.timecode,
        };
      }
      return attributes;
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
