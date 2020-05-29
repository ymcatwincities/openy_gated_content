<template>
  <div class="gated-content-video-page">
    <div class="text-center my-4">
      <router-link :to="{ name: 'Home' }">Home</router-link>
    </div>
    Live stream page.
    <div v-if="loading">Loading</div>
    <div v-else-if="error">Error loading</div>
    <div v-else>
      <h1>{{ video.attributes.title }}</h1>
      <div>Start: {{ video.attributes.date.value }}</div>
      <div>End: {{ video.attributes.date.end_value }}</div>
      <div>Description:</div>
      <div v-html="video.attributes.description.processed"></div>
      <div>Category: {{ category }}</div>
      <pre>Media: {{ media }}</pre>
    </div>
  </div>
</template>

<script>
import client from '@/client';

export default {
  name: 'LiveStreamPage',
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
      params: [
        'field_ls_category',
        'field_ls_media',
        // Data from parent (series).
        'category',
        'media',
      ],
    };
  },
  computed: {
    media() {
      // This value most of all from parent (series), but can be overridden by item,
      // so ve need to check this here and use correct media value.
      return this.video.attributes.field_ls_media ? this.video.attributes.field_ls_media
        : this.video.attributes.media;
    },
    category() {
      // Same for category.
      return this.video.attributes.field_ls_category ? this.video.attributes.field_ls_category.name
        : this.video.attributes.category.name;
    },
  },
  mounted() {
    const params = {};
    if (this.params) {
      params.include = this.params.join(',');
    }
    client
      .get(`jsonapi/eventinstance/live_stream/${this.id}`, { params })
      .then((response) => {
        this.video = response.data.data;
        this.combine(response.data);
        this.loading = false;
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
        if (rel === null) {
          this.video.attributes[field] = null;
          return;
        }
        // Multi-value fields.
        if (Array.isArray(rel)) {
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
