<template>
  <div class="gated-content-video-page">
    <div class="text-center my-4">
      <router-link :to="{ name: 'Home' }">Home</router-link>
    </div>
    Video page.
    <div v-if="loading">Loading</div>
    <div v-else-if="error">Error loading</div>
    <div v-else>
      <h1>{{ video.attributes.title }}</h1>
      <div v-html="video.attributes.field_gc_video_description.processed"></div>
      <div>{{ video.attributes.field_gc_video_category }}</div>
      <pre>{{ video.attributes }}</pre>
    </div>
  </div>
</template>

<script>
import client from '@/client';

export default {
  name: 'VideoPage',
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      video: null,
      response: null,
      params: ['field_gc_video_category', 'field_gc_video_media', 'field_gc_video_equipment'],
    };
  },
  mounted() {
    const params = {};
    if (this.params) {
      params.include = this.params.join(',');
    }
    client
      .get(`jsonapi/node/gc_video/${this.id}`, { params })
      .then((response) => {
        this.loading = false;
        this.video = response.data.data;
        this.combine(response.data);
      })
      .catch((error) => {
        this.error = true;
        this.loading = false;
        console.error(error);
        throw error;
      });
  },
  methods: {
    combine(data) {
      if (!data.included) return;
      this.params.forEach((field) => {
        const rel = data.data.relationships[field].data;
        if (Array.isArray(rel)) {
          // Multi-value fields.
          this.video.attributes[field] = [];
          rel.forEach((relItem) => {
            this.video.attributes[field].push(
              data.included
                .find((obj) => obj.type === relItem.type && obj.id === relItem.id)
                .attributes,
            );
          });
        } else {
          // Single-value fields.
          this.video.attributes[field] = data.included
            .find((obj) => obj.type === rel.type && obj.id === rel.id)
            .attributes;
        }
      });
    },
  },
};
</script>

<style>

</style>
