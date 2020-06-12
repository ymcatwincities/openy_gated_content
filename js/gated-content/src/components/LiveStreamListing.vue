<template>
  <div class="videos gated-container">
    <div class="videos__header" :class="{'with-date-filter': withDateFilter}">
      <h2 class="title">{{ title }}</h2>
      <h2 class="videos__date-filter"
        v-if="withDateFilter"
      >
        <button v-on:click.stop="backOneDay" class="left" role="button"
                :style="[hidePrevDateButton ? {'visibility':'hidden'}:'']"
                aria-label="previous date"><i class="fa fa-angle-left"></i></button>
        <span class="date" v-cloak>{{ dateFormatted }}</span>
        <button v-on:click.stop="forwardOneDay" class="right"
                role="button" aria-label="next date"><i class="fa fa-angle-right"></i></button>
      </h2>
      <router-link :to="{ name: 'LiveStreamListing' }" v-if="viewAll">
        View All
      </router-link>
    </div>
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
    withDateFilter: {
      type: Boolean,
      default: false,
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
      date: new Date(),
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
    dateFormatted() {
      const weekDay = this.date.toLocaleDateString('en', { weekday: 'long' });
      const monthName = this.date.toLocaleDateString('en', { month: 'long' });
      return `${monthName} ${this.date.getDate()}, ${weekDay}`;
    },
    hidePrevDateButton() {
      const isToday = (someDate) => {
        const today = new Date();
        return (someDate.getDate() === today.getDate()
          && someDate.getMonth() === today.getMonth()
          && someDate.getFullYear() === today.getFullYear());
      };

      return isToday(this.date);
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
              1,
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
              59,
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
    backOneDay() {
      this.date = new Date(this.date.setTime(this.date.getTime() - 86400000));
    },
    forwardOneDay() {
      this.date = new Date(this.date.setTime(this.date.getTime() + 86400000));
    },
  },
};
</script>

<style lang="scss">
</style>
