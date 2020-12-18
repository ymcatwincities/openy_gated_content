<template>
  <div class="gated-container">
    <div class="listing-header">
      <h2 class="title" v-if="title !== 'none'">{{ title }}</h2>
      <router-link
        :to="{ name: 'BlogsListing', query: { type: category } }"
        v-if="viewAll && listingIsNotEmpty"
        class="view-all"
      >
        More
      </router-link>
    </div>
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <template v-else-if="listingIsNotEmpty">
      <div v-if="error">Error loading</div>
      <div v-else class="four-columns">
        <BlogTeaser
          v-for="blog in listing"
          :key="blog.id"
          :blog="blog"
        />
      </div>
    </template>
    <div v-else class="empty-listing">
      Blog posts not found.
    </div>
    <Pagination
      v-if="pagination"
      :links="links"
    ></Pagination>
  </div>
</template>

<script>
import client from '@/client';
import BlogTeaser from '@/components/blog/BlogTeaser.vue';
import Spinner from '@/components/Spinner.vue';
import Pagination from '@/components/Pagination.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FavoritesMixin } from '@/mixins/FavoritesMixin';

export default {
  name: 'BlogListing',
  mixins: [JsonApiCombineMixin, SettingsMixin, FavoritesMixin],
  components: {
    BlogTeaser,
    Spinner,
    Pagination,
  },
  props: {
    title: {
      type: String,
      default: 'Blog posts',
    },
    excludedId: {
      type: String,
      default: '',
    },
    msg: String,
    viewAll: {
      type: Boolean,
      default: false,
    },
    featured: {
      type: Boolean,
      default: false,
    },
    sort: {
      type: Object,
      default() {
        return { path: 'created', direction: 'DESC' };
      },
    },
    pagination: {
      type: Boolean,
      default: false,
    },
    limit: {
      type: Number,
      default: 0,
    },
    category: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      listing: [],
      links: {},
      featuredLocal: false,
      params: [
        'field_vy_blog_image',
        'field_vy_blog_image.field_media_image',
      ],
    };
  },
  watch: {
    $route: 'load',
    excludedVideoId: 'load',
    sort: 'load',
  },
  async mounted() {
    // By default emit that listing not empty to the parent component.
    this.$emit('listing-not-empty', true);
    this.featuredLocal = this.featured;
    await this.load();
  },
  computed: {
    listingIsNotEmpty() {
      return this.listing !== null && this.listing.length > 0;
    },
  },
  methods: {
    async load() {
      this.loading = true;
      const params = {};
      if (this.params) {
        params.include = this.params.join(',');
      }

      params.sort = {
        sortBy: this.sort,
      };

      params.filter = {};
      if (this.excludedId.length > 0) {
        params.filter.excludeSelf = {
          condition: {
            path: 'id',
            operator: '<>',
            value: this.excludedId,
          },
        };
      }

      if (this.favorites) {
        if (this.isFavoritesTypeEmpty('node', 'vy_blog_post')) {
          this.loading = false;
          return;
        }
        params.filter.includeFavorites = {
          condition: {
            path: 'drupal_internal__nid',
            operator: 'IN',
            value: this.getFavoritesTypeIds('node', 'vy_blog_post'),
          },
        };
      }

      if (this.category) {
        params.filter['field_gc_video_category.id'] = this.category;
      }
      if (this.featuredLocal) {
        params.filter.field_gc_video_featured = 1;
      }
      params.filter.status = 1;
      if (this.pagination) {
        const currentPage = parseInt(this.$route.query.page, 10) || 0;
        params.page = {
          limit: this.config.pager_limit,
          offset: currentPage * this.config.pager_limit,
        };
      } else if (this.limit !== 0) {
        params.page = {
          limit: this.limit,
        };
      }

      client
        .get('jsonapi/node/vy_blog_post', { params })
        .then((response) => {
          this.links = response.data.links;
          this.listing = this.combineMultiple(
            response.data.data,
            response.data.included,
            this.params,
          );
          if (this.featuredLocal === true && this.listing.length === 0) {
            // Load one more time without featured filter.
            this.featuredLocal = false;
            this.load();
          }
          if (this.listing === null || this.listing.length === 0) {
            // Emit that listing empty to the parent component.
            this.$emit('listing-not-empty', false);
          }
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
