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
      <div>Category: {{ video.attributes.field_gc_video_category.name }}</div>
      <div>Level: {{ video.attributes.field_gc_video_level.name }}</div>
      <div>Equipment:
        <ul>
          <li v-for="equip in video.attributes.field_gc_video_equipment"
              :key="equip.drupal_internal__tid">
            {{ equip.name }}
          </li>
        </ul>
      </div>
      <div>Instructor: {{ video.attributes.field_gc_video_instructor }}</div>
      <div>Duration: {{ video.attributes.field_gc_video_duration }}</div>
      <hr />
      <pre>Media: {{ video.attributes.field_gc_video_media }}</pre>
      <pre>Video: {{ video.attributes }}</pre>
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
      params: [
        'field_gc_video_category',
        'field_gc_video_media',
        'field_gc_video_equipment',
        'field_gc_video_level',
      ],
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
