<template>
  <div>
    <LazyYoutubeVideo ref="youtube-player" v-if="source === 'youtube'" :src="youtubeSrc"/>
    <vueVimeoPlayer
      :class="source"
      ref="vimeo-player"
      v-if="source === 'vimeo' || source === 'vimeo_event'"
      v-bind="vimeoOptions"
    />
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
      const options = {
        videoId: this.media.field_media_video_id,
        'video-url': this.media.field_media_video_embed_field,
        playerWidth: undefined,
        playerHeight: undefined,
        options: {
          responsive: true,
        },
      };

      return options;
    },
  },
};
</script>
