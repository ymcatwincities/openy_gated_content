<template>
  <div class="video-teaser">
    <router-link :to="{ name: 'Video', params: { id: video.id } }">
      <img :src="image" :alt="video.attributes.title">
      <h3>{{ video.attributes.title }}</h3>
    </router-link>
  </div>
</template>

<script>

export default {
  name: 'VideoTeaser',
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
