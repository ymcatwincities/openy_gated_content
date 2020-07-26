<template>
  <div class="gated-content-blog-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="blog-page__image" v-bind:style="{
              backgroundImage: `url(${image})`
      }">
      </div>
      <div class="blog-header gated-container">
        <h2>{{ blog.attributes.title }}</h2>
      </div>
      <div class="blog-content gated-container">
        <div
          v-if="blog.attributes.field_vy_blog_description"
          class="blog-content__description"
          v-html="blog.attributes.field_vy_blog_description.processed"
        ></div>
      </div>

      <BlogListing
        :title="'NEXT BLOG POSTS'"
        :excluded-id="blog.id"
        :viewAll="true"
        :limit="6"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import Spinner from '@/components/Spinner.vue';
import BlogListing from '@/components/blog/BlogListing.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';

export default {
  name: 'BlogPage',
  mixins: [JsonApiCombineMixin],
  components: {
    BlogListing,
    Spinner,
  },
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
      blog: {},
      response: null,
      params: [
        'field_vy_blog_image',
        'field_vy_blog_image.field_media_image',
      ],
    };
  },
  computed: {
    image() {
      if (!this.blog.attributes['field_vy_blog_image.field_media_image']) {
        return null;
      }

      return this.blog.attributes['field_vy_blog_image.field_media_image']
        .uri.url;
    },
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
      const params = {};
      if (this.params) {
        params.include = this.params.join(',');
      }
      client
        .get(`jsonapi/node/vy_blog_post/${this.id}`, { params })
        .then((response) => {
          this.blog = this.combine(response.data.data, response.data.included, this.params);
          this.loading = false;
        }).then(() => {
          this.$log.trackEventEntityView('node', 'vy_blog_post', this.blog.attributes.drupal_internal__nid);
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
