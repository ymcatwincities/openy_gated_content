<template>
  <div class="video-teaser">
    <router-link :to="{ name: 'Video', params: { id: video.id } }">
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
  },
  mounted() {
  },
};
</script>

<style lang="scss">

</style>
