<template>
  <div class="video-teaser">
    <router-link :to="{ name: 'Video', params: { id: video.id } }">
        <div class="preview" v-bind:style="{
              backgroundImage: `url(${image})`
            }">
          <YoutubePlayButton></YoutubePlayButton>
          <div v-if="duration" class="duration">{{duration}}</div>
        </div>
        <div class="title">{{ video.attributes.title }}</div>
        <div class="meta">
          <div class="video-level">
            {{ video.attributes.field_gc_video_level.name | first_letter }}
          </div>
          {{ video.attributes.field_gc_video_level.name | capitalize }}
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
    duration() {
      const sec = this.video.attributes.field_gc_video_duration;
      if (sec === null) {
        return '';
      }

      function appendZero(n) {
        return (n < 10) ? `0${n}` : n;
      }

      return `${appendZero(Math.floor(sec / 60))}:${appendZero(sec % 60)}`;
    },
  },
  mounted() {
  },
};
</script>

<style lang="scss">

</style>
