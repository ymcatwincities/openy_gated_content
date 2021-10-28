<template>
  <div class="gated-content-blog-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div
        v-if="image !== null"
        class="blog-page__image"
        :style="{ backgroundImage: `url(${getStyledUrl(image, 'carnation_banner_1920_700')})` }"
      >
      </div>
      <div class="gated-containerV2 my-40-20 px--20-10 text-black">
        <h2 class="cachet-book-32-28">{{ blog.attributes.title }}</h2>
        <div v-if="blogCategories" class="blog-page__categories">
          <b v-if="blogCategories.length === 1">Category: </b>
          <b v-else>Categories: </b>
          <ul>
            <li class="blog-page__category" v-for="tid in blogCategories" :key="tid">
              <CategoryLinks :tid="tid" />
            </li>
          </ul>
        </div>
        <AddToFavorite
          :id="blog.attributes.drupal_internal__nid"
          :type="'node'"
          :bundle="'vy_blog_post'"
          class="rounded-border border-concrete mt-20-10"
        ></AddToFavorite>
      </div>
      <div class="gated-containerV2 my-40-20 px--20-10 text-black">
        <div
          v-if="blog.attributes.field_vy_blog_description"
          class="blog-content__description verdana-16-14"
          v-html="blog.attributes.field_vy_blog_description.processed"
        ></div>
      </div>

      <BlogListing
        v-if="blogCategories"
        :title="config.components.vy_blog_post.up_next_title"
        :excluded-id="blog.id"
        :categories="blogCategories"
        :viewAll="true"
        :limit="8"
        class="my-40-20"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import AddToFavorite from '@/components/AddToFavorite.vue';
import Spinner from '@/components/Spinner.vue';
import BlogListing from '@/components/blog/BlogListing.vue';
import CategoryLinks from '@/components/category/CategoryLinks.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { ImageStyleMixin } from '@/mixins/ImageStyleMixin';

export default {
  name: 'BlogPage',
  mixins: [JsonApiCombineMixin, SettingsMixin, ImageStyleMixin],
  components: {
    AddToFavorite,
    BlogListing,
    Spinner,
    CategoryLinks,
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
      return this.blog.attributes['field_vy_blog_image.field_media_image'];
    },
    blogCategories() {
      const fieldValues = this.blog.attributes.field_gc_video_category;
      if (!fieldValues || fieldValues.length === 0) {
        return null;
      }
      return fieldValues.map((category) => category.drupal_internal__tid);
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
