<template>
  <div class="video-teaser">
    <img :src="image" :alt="video.attributes.title">
    <router-link :to="{ name: 'LiveStream', params: { id: video.id } }">
      <h3>{{ video.attributes.title }}</h3>
    </router-link>
    <div>Start: {{ video.attributes.date.value }}</div>
    <div>End: {{ video.attributes.date.end_value }}</div>
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
      const vid = this.video.attributes.field_ls_media
        // Use event instance value.
        ? this.video.attributes.field_gc_video_media.field_media_video_id
        // Use parent (series) value.
        : this.video.attributes.media.field_media_video_id;
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
// TODO: delete this temp style
.video-teaser {
  border: solid 1px #0d0d0d;
  padding: 10px;
  margin: 10px;
  width: 40%;
  float: left;
}
</style>
