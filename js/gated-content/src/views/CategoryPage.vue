<template>
  <div class="gated-content-category-page">
    <div v-if="loading">Loading</div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="category-details">
        <div class="gated-container">
          <h2>{{ category.attributes.name }}</h2>
          <div
            v-if="category.attributes.description"
            v-html="category.attributes.description.processed"
            class="mb-3"
          ></div>
        </div>
      </div>
      <div class="back-to-categories-wrapper">
        <div class="gated-container">
          <router-link :to="{ name: 'CategoryListing' }">← Back to all categories</router-link>
        </div>
      </div>
      <VideoListing
        v-if="type === 'video'"
        :title="config.components.gc_video.title"
        :category="category.id"
        :pagination="true"
      />
      <BlogListing
        v-if="type === 'blog'"
        :title="config.components.vy_blog_post.title"
        :category="category.id"
        :pagination="true"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import VideoListing from '@/components/video/VideoListing.vue';
import BlogListing from '@/components/blog/BlogListing.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'CategoryPage',
  mixins: [JsonApiCombineMixin, SettingsMixin],
  components: {
    VideoListing,
    BlogListing,
  },
  props: {
    type: {
      type: String,
      required: true,
      validator(value) {
        // Can be video or blog.
        return ['video', 'blog'].indexOf(value) !== -1;
      },
    },
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
