<template>
  <div class="video-teaser">
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
  },
  mounted() {
  },
};
</script>

<style lang="scss">

</style>
