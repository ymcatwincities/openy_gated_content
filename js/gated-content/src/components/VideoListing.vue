<template>
  <div>
    <h2 class="title">{{ title }}</h2>
    <div v-if="loading">Loading...</div>
    <div v-else-if="error">Error loading</div>
    <div v-else class="video-listing">
      <VideoTeaser
        v-for="video in filteredListing"
        :key="video.id"
        :video="video"
      />
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
    title: {
      type: String,
      default: 'Videos',
    },
    excludedVideoId: Number,
    msg: String,
  },
  data() {
    return {
      loading: true,
      error: false,
      listing: null,
      params: [
        'field_gc_video_media',
      ],
    };
  },
  mounted() {
    const params = {};
    if (this.params) {
      params.include = this.params.join(',');
    }
    client
      .get('jsonapi/node/gc_video', { params })
      .then((response) => {
        this.loading = false;
        this.listing = response.data.data;
        this.combine(response.data);
      })
      .catch((error) => {
        this.error = true;
        this.loading = false;
        console.error(error);
        throw error;
      });
  },
  computed: {
    filteredListing() {
      return this.listing.filter((video) => video.id !== this.excludedVideoId);
    },
  },
  methods: {
    // TODO: maybe we need to move this method to mixin?
    // (note: this can be singe or multiple values, compare LiveStreamListing and LiveStreamPage)
    combine(data) {
      if (!data.included) return;
      this.listing.forEach((video, key) => {
        this.params.forEach((field) => {
          const rel = video.relationships[field].data;
          // Multi-value fields.
          if (Array.isArray(rel)) {
            this.listing[key].attributes[field] = [];
            rel.forEach((relItem) => {
              this.listing[key].attributes[field].push(
                data.included
                  .find((obj) => obj.type === relItem.type && obj.id === relItem.id)
                  .attributes,
              );
            });
          } else {
            // Single-value fields.
            this.listing[key].attributes[field] = data.included
              .find((obj) => obj.type === rel.type && obj.id === rel.id)
              .attributes;
          }
        });
      });
    },
  },
};
</script>

<style lang="scss">
</style>
