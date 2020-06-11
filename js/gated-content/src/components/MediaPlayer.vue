<template>
  <div>
    <LazyYoutubeVideo ref="youtube-player" v-if="source === 'youtube'" :src="youtubeSrc"/>
    <vueVimeoPlayer
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
        playerWidth: undefined,
        playerHeight: undefined,
        options: {
          responsive: true,
        },
      };

      if (/https:\/\/vimeo\.com\/\d+\/[a-z0-9]+/i.test(this.media.field_media_video_embed_field)
        || /https:\/\/vimeo\.com\/event\/\d+/i.test(this.media.field_media_video_embed_field)
      ) {
        // In case we have private video or event - set video-url option.
        // Example of private url - https://vimeo.com/426932693/cfbe98b981
        options['video-url'] = this.media.field_media_video_embed_field;
      }

      return options;
    },
  },
};
</script>

<style lang="scss">

</style>
