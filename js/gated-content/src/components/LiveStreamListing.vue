<template>
  <div class="gated-content-videos">
    <h2>Live streams</h2>
    <div v-if="loading">Loading...</div>
    <div v-else-if="error">Error loading</div>
    <div v-else>
      <div v-for="video in listing" :key="video.id">
        <LiveStreamTeaser :video="video" />
      </div>
    </div>
  </div>
</template>

<script>
import client from '@/client';
import LiveStreamTeaser from '@/components/LiveStreamTeaser.vue';

export default {
  name: 'LiveStreamListing',
  components: {
    LiveStreamTeaser,
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
      // TODO: maybe we need sort or filter here.
      .get('jsonapi/eventinstance/live_stream')
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
