<template>
  <div class="gated-content-category-page">
    <div v-if="loading">Loading</div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="category-details gated-container">
        <router-link :to="{ name: 'CategoryListing' }">← Back to all categories</router-link>
        <h2>{{ category.attributes.name }}</h2>
        <div
          v-if="category.attributes.description"
          v-html="category.attributes.description.processed"
        ></div>
      </div>
      <VideoListing
        :title="'Videos'"
        :category="category.id"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import 'vue-lazy-youtube-video/dist/style.css';
import VideoListing from '../components/VideoListing.vue';
import { JsonApiCombineMixin } from '../mixins/JsonApiCombineMixin';

export default {
  name: 'CategoryPage',
  mixins: [JsonApiCombineMixin],
  components: {
    VideoListing,
  },
  props: {
    cid: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      category: null,
      response: null,
    };
  },
  watch: {
    $route: 'load',
  },
  async mounted() {
    await this.load();
  },
  methods: {
    async load() {
      this.loading = true;
      client
        .get(`jsonapi/taxonomy_term/gc_category/${this.cid}`)
        .then((response) => {
          this.category = response.data.data;
          this.loading = false;
        })
        .catch((error) => {
          this.error = true;
          this.loading = false;
          console.error(error);
          throw error;
        });
    },
  },
};
</script>

<style>

</style>
