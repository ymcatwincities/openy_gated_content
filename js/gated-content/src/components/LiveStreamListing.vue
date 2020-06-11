<template>
  <div>
    <h2 class="title">{{ title }}</h2>
    <router-link :to="{ name: 'LiveStreamListing' }" v-if="viewAll">
      View All
    </router-link>
    <template v-if="listingIsNotEmpty">
      <div v-if="loading">Loading...</div>
      <div v-else-if="error">Error loading</div>
      <div v-else class="video-listing live-stream-listing">
          <LiveStreamTeaser
            v-for="video in listing"
            :key="video.id"
            :video="video"
          />
      </div>
    </template>
    <div v-else class="empty-listing">
      Live streams not found.
    </div>
  </div>
</template>

<script>
import client from '@/client';
import LiveStreamTeaser from '@/components/LiveStreamTeaser.vue';
import { JsonApiCombineMixin } from '../mixins/JsonApiCombineMixin';

export default {
  name: 'LiveStreamListing',
  mixins: [JsonApiCombineMixin],
  components: {
    LiveStreamTeaser,
  },
  props: {
    title: {
      type: String,
      default: 'Live streams',
    },
    excludedVideoId: {
      type: String,
      default: '',
    },
    viewAll: {
      type: Boolean,
      default: false,
    },
    date: {
      type: Date,
      default: null,
    },
    featured: {
      type: Boolean,
      default: false,
    },
    limit: {
      type: Number,
      default: 0,
    },
    msg: String,
  },
  data() {
    return {
      loading: true,
      error: false,
      listing: null,
      featuredLocal: false,
      params: [
        'field_ls_image',
        'field_ls_image.field_media_image',
        'field_ls_level',
        'image',
        'image.field_media_image',
        'level',
      ],
    };
  },
  watch: {
    $route: 'load',
    excludedVideoId: 'load',
    date: 'load',
  },
  async mounted() {
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
      const params = {};
      if (this.params) {
        params.include = this.params.join(',');
      }

      params.filter = {
        dateFilter: {
          condition: {
            path: 'date.end_value',
            operator: '>=',
            value: new Date().toISOString(),
          },
        },
      };

      if (this.date) {
        params.filter.dateFilterStart = {
          condition: {
            path: 'date.value',
            operator: '>',
            value: new Date(
              this.date.getFullYear(),
              this.date.getMonth(),
              this.date.getDate(),
              0,
              0,
              1
            ),
          },
        };
        params.filter.dateFilterEnd = {
          condition: {
            path: 'date.value',
            operator: '<',
            value: new Date(
              this.date.getFullYear(),
              this.date.getMonth(),
              this.date.getDate(),
              23,
              59,
              59
            ),
          },
        };
      }

      if (this.excludedVideoId.length > 0) {
        params.filter.excludeSelf = {
          condition: {
            path: 'id',
            operator: '<>',
            value: this.excludedVideoId,
          },
        };
      }

      if (this.limit !== 0) {
        params.page = {
          limit: 6,
        };
      }

      if (this.featuredLocal) {
        params.filter.field_ls_featured = 1;
      }

      params.sort = {
        sortByDate: {
          path: 'date.value',
          direction: 'ASC',
        },
      };

      client
        .get('jsonapi/eventinstance/live_stream', { params })
        .then((response) => {
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

<style lang="scss">
</style>
