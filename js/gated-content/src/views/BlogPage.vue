<template>
  <div class="gated-content-blog-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div
        v-if="blog.attributes.field_vy_blog_image !== null"
        class="blog-page__image"
        v-bind:style="{backgroundImage: `url(${image})`}"
      >
      </div>
      <div class="blog-header gated-container">
        <h2>{{ blog.attributes.title }}</h2>
        <div
          v-if="blog.attributes.field_gc_video_category &&
            blog.attributes.field_gc_video_category.length > 0"
          class="blog-category"
        >
          <b>Category: </b>
          <span v-for="(category_data, index) in blog.relationships.field_gc_video_category.data"
                :key="index">
            <router-link :to="{
              name: 'Category',
              params: {
                cid: category_data.id,
                type: 'blog'
              }
            }">{{ blog.attributes.field_gc_video_category[index].name }}</router-link>
            <i v-if="index !== blog.attributes.field_gc_video_category.length - 1"> | </i>
          </span>
          <AddToFavorite
            :id="blog.attributes.drupal_internal__nid"
            :type="'node'"
            :bundle="'vy_blog_post'"
          ></AddToFavorite>
        </div>
      </div>
      <div class="blog-content gated-container">
        <div
          v-if="blog.attributes.field_vy_blog_description"
          class="blog-content__description"
          v-html="blog.attributes.field_vy_blog_description.processed"
        ></div>
      </div>

      <BlogListing
        v-if="firstCategory"
        :title="config.components.vy_blog_post.up_next_title"
        :excluded-id="blog.id"
        :category="firstCategory"
        :viewAll="true"
        :limit="6"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import AddToFavorite from '@/components/AddToFavorite.vue';
import Spinner from '@/components/Spinner.vue';
import BlogListing from '@/components/blog/BlogListing.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'BlogPage',
  mixins: [JsonApiCombineMixin, SettingsMixin],
  components: {
    AddToFavorite,
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
        'field_gc_video_category',
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
    firstCategory() {
      if (
        !this.blog.relationships.field_gc_video_category.data
        || this.blog.relationships.field_gc_video_category.data.length === 0
      ) {
        return null;
      }
      return this.blog.relationships.field_gc_video_category.data[0].id;
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
          this.$log.trackEvent('entityView', 'node', 'vy_blog_post', this.blog.attributes.drupal_internal__nid);
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
