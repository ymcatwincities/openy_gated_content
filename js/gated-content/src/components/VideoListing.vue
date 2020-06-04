<template>
  <div v-if="listingIsNotEmpty">
    <h2 class="title">{{ title }}</h2>
    <router-link :to="{ name: 'CategoryListing' }" v-if="viewAll">
      View All
    </router-link>
    <div v-if="loading">Loading...</div>
    <div v-else-if="error">Error loading</div>
    <div v-else class="video-listing">
      <VideoTeaser
        v-for="video in listing"
        :key="video.id"
        :video="video"
      />
    </div>
  </div>
</template>

<script>
import client from '@/client';
import VideoTeaser from '@/components/VideoTeaser.vue';
import { JsonApiCombineMixin } from '../mixins/JsonApiCombineMixin';

export default {
  name: 'VideoListing',
  mixins: [JsonApiCombineMixin],
  components: {
    VideoTeaser,
  },
  props: {
    title: {
      type: String,
      default: 'Videos',
    },
    excludedVideoId: {
      type: String,
      default: '',
    },
    msg: String,
    category: {
      type: String,
      default: '',
    },
    featured: {
      type: Boolean,
      default: false,
    },
    viewAll: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      listing: null,
      params: [
        'field_gc_video_media',
        'field_gc_video_level',
        'field_gc_video_category',
      ],
    };
  },
  watch: {
    $route: 'load',
    excludedVideoId: 'load',
  },
  async mounted() {
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

      params.filter = {};
      if (this.excludedVideoId.length > 0) {
        params.filter.excludeSelf = {
          condition: {
            path: 'id',
            operator: '<>',
            value: this.excludedVideoId,
          },
        };
      }

      if (this.category.length > 0) {
        params.filter['field_gc_video_category.id'] = this.category;
      }

      if (this.featured) {
        params.filter.field_gc_video_featured = 1;
        params.page = {
          limit: 6,
        };
      }

      client
        .get('jsonapi/node/gc_video', { params })
        .then((response) => {
          this.listing = this.combineMultiple(
            response.data.data,
            response.data.included,
            this.params,
          );
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
