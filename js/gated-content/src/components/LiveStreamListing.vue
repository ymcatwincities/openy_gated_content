<template>
  <div v-if="listingIsNotEmpty">
    <h2 class="title">{{ title }}</h2>
    <div v-if="loading">Loading...</div>
    <div v-else-if="error">Error loading</div>
    <div v-else class="video-listing live-stream-listing">
        <LiveStreamTeaser
          v-for="video in listing"
          :key="video.id"
          :video="video"
        />
    </div>
  </div>
</template>

<script>
import client from '@/client';
import LiveStreamTeaser from '@/components/LiveStreamTeaser.vue';

export default {
  name: 'LiveStreamListing',
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
  mounted() {
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

    params.sort = {
      sortByDate: {
        path: 'date.end_value',
        direction: 'ASC',
      },
    };

    client
      // TODO: maybe we need sort or filter here.
      .get('jsonapi/eventinstance/live_stream', { params })
      .then((response) => {
        this.loading = false;
        this.listing = response.data.data;
        this.combine(response.data);
      })
      .catch((error) => {
        this.error = true;
        this.loading = false;
        console.error(error);
        throw error;
      });
  },
  computed: {
    listingIsNotEmpty() {
      return this.listing !== null && this.listing.length > 0;
    },
  },
  methods: {
    combine(data) {
      if (!data.included) return;
      this.listing.forEach((video, key) => {
        this.params.forEach((field) => {
          const rel = video.relationships[field].data;
          if (rel === null) {
            this.listing[key].attributes[field] = null;
            return;
          }
          // Multi-value fields.
          if (Array.isArray(rel)) {
            this.listing[key].attributes[field] = [];
            rel.forEach((relItem) => {
              this.listing[key].attributes[field].push(
                data.included
                  .find((obj) => obj.type === relItem.type && obj.id === relItem.id)
                  .attributes,
              );
            });
          } else {
            // Single-value fields.
            this.listing[key].attributes[field] = data.included
              .find((obj) => obj.type === rel.type && obj.id === rel.id)
              .attributes;
          }
        });
      });
    },
  },
};
</script>

<style lang="scss">
</style>
