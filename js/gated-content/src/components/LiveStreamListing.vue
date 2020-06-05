<template>
  <div>
    <h2 class="title">{{ title }}</h2>
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
      Listing is empty.
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
    msg: String,
  },
  data() {
    return {
      loading: true,
      error: false,
      listing: null,
      params: [
        'field_ls_media',
        'field_ls_level',
        'media',
        'level',
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

      params.filter = {
        dateFilter: {
          condition: {
            path: 'date.end_value',
            operator: '>=',
            value: new Date().toISOString(),
          },
        },
      };

      if (this.excludedVideoId.length > 0) {
        params.filter.excludeSelf = {
          condition: {
            path: 'id',
            operator: '<>',
            value: this.excludedVideoId,
          },
        };
      }

      if (!this.viewAll) {
        params.page = {
          limit: 6,
        };
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
