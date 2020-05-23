<template>
  <div class="gated-content-videos">
    <h1>Open Y Gated content</h1>
    <div v-if="loading">Loading...</div>
    <div v-else-if="error">Error loading</div>
    <div v-else>
      <div v-for="video in listing" :key="video.id">
        <VideoTeaser :video="video" />
      </div>
      <pre>{{ listing }}</pre>
    </div>
  </div>
</template>

<script>
import client from '@/client';
import VideoTeaser from '@/components/VideoTeaser.vue';

export default {
  name: 'VideoListing',
  components: {
    VideoTeaser,
  },
  props: {
    msg: String,
  },
  data() {
    return {
      loading: true,
      error: false,
      listing: null,
    };
  },
  mounted() {
    client
      .get('jsonapi/node/gc_video')
      .then((response) => {
        this.loading = false;
        this.listing = response.data.data;
      })
      .catch((error) => {
        this.error = true;
        this.loading = false;
        console.error(error);
        throw error;
      });
  },
};
</script>

<style lang="scss">
.gated-content-videos {
  h1 {
    color: red;
  }
}
</style>
