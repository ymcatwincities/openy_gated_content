<template>
  <div class="video-teaser">
    <router-link :to="{ name: 'Video', params: { id: video.id } }">
        <div class="preview" v-bind:style="{
              backgroundImage: `url(${image})`
            }">
          <YoutubePlayButton></YoutubePlayButton>
          <div class="duration">00:0{{item}}</div>
        </div>
        <div class="title">{{ video.attributes.title }}</div>
        <div class="meta">
          <div class="video-level">M</div> Moderate
        </div>
    </router-link>
  </div>
</template>

<script>
import YoutubePlayButton from './YoutubePlayButton.vue';

export default {
  name: 'VideoTeaser',
  components: {
    YoutubePlayButton,
  },
  props: {
    video: {
      type: Object,
      required: true,
    },
  },
  computed: {
    image() {
      const vid = this.video.attributes.field_gc_video_media.field_media_video_id;
      // Possible images resolutions here:
      // default.jpg
      // hqdefault.jpg
      // sddefault.jpg
      // maxresdefault.jpg
      return `https://img.youtube.com/vi/${vid}/mqdefault.jpg`;
    },
  },
  mounted() {
  },
};
</script>

<style lang="scss">

</style>
