<template>
  <div class="video">
    <LazyYoutubeVideo ref="youtube-player" v-if="source === 'youtube'" :src="youtubeSrc"/>
    <vueVimeoPlayer ref="vimeo-player" v-if="source === 'vimeo'" v-bind="vimeoOptions"/>
  </div>
</template>

<script>
import LazyYoutubeVideo from 'vue-lazy-youtube-video';
import 'vue-lazy-youtube-video/dist/style.css';
import { vueVimeoPlayer } from 'vue-vimeo-player';

export default {
  name: 'MediaPlayer',
  components: {
    LazyYoutubeVideo,
    vueVimeoPlayer,
  },
  props: {
    media: {
      type: Object,
      required: true,
    },
  },
  computed: {
    source() {
      return this.media.field_media_source;
    },
    youtubeSrc() {
      return `https://www.youtube.com/embed/${this.media.field_media_video_id}`;
    },
    vimeoOptions() {
      return {
        videoId: this.media.field_media_video_id,
        // TODO:
        // https://www.npmjs.com/package/vue-vimeo-player
        // According to doc we can use "video-url" (if using private links) - need to test this.
        // Also there an issue about Private Videos:
        // https://github.com/dobromir-hristov/vue-vimeo-player/issues/13
        playerWidth: undefined,
        playerHeight: undefined,
        options: {
          responsive: true,
        },
      };
    },
  },
};
</script>

<style lang="scss">

</style>
