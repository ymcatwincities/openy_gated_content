<template>
  <div :class="media.field_media_source">
    <VueVideoWrapper
      ref="player"
      :player="player"
      :videoId="videoId"
      :options="{responsive: 'true', url: media.field_media_video_embed_field}"
      @loaded="$refs.player.pause()"
      @play="handlePlay()"
      @ended="handlePlayerEvent('videoPlaybackEnded')"
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
      if (this.playbackLogged) {
        return;
      }
      this.playbackLogged = true;
      this.handlePlayerEvent('videoPlaybackStarted');
    },
  },
  updated() {
    this.playbackLogged = false;
  },
};
</script>
