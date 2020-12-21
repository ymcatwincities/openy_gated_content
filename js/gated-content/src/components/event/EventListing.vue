<template>
  <div class="gated-container">
    <div class="listing-header" :class="{'with-date-filter': withDateFilter}">
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
      <router-link :to="{ name: viewAllRoute }" v-if="viewAll" class="view-all">
        More
      </router-link>
    </div>
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <template v-else-if="listingIsNotEmpty">
      <div v-if="error">Error loading</div>
      <div v-else class="four-columns">
        <EventTeaser
          v-for="video in listing"
          :key="video.id"
          :video="video"
        />
      </div>
    </template>
    <div v-else class="empty-listing">{{ msg }}</div>
  </div>
</template>

<script>
import client from '@/client';
import EventTeaser from '@/components/event/EventTeaser.vue';
import Spinner from '@/components/Spinner.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { FavoritesMixin } from '@/mixins/FavoritesMixin';

export default {
  name: 'EventListing',
  mixins: [JsonApiCombineMixin, FavoritesMixin],
  components: {
    EventTeaser,
    Spinner,
  },
  props: {
    title: {
      type: String,
      default: 'Live streams',
    },
    eventType: {
      type: String,
      default: 'live_stream',
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
    category: {
      type: String,
      default: '',
    },
    sort: {
      type: Object,
      default() {
        return { path: 'date.value', direction: 'ASC' };
      },
    },
    limit: {
      type: Number,
      default: 0,
    },
    msg: {
      String,
      default: 'Events not found.',
    },
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
      oneDay: 86400000,
    };
  },
  watch: {
    $route: 'load',
    excludedVideoId: 'load',
    date: 'load',
    eventType: 'load',
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
    viewAllRoute() {
      switch (this.eventType) {
        case 'live_stream':
          return 'LiveStreamListing';
        case 'virtual_meeting':
          return 'VirtualMeetingListing';
        default:
          return 'LiveStreamListing';
      }
    },
  },
  methods: {
    async load() {
      this.loading = true;
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

      if (this.withDateFilter) {
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

      if (this.favorites) {
        if (this.isFavoritesTypeEmpty('eventinstance', this.eventType)) {
          this.loading = false;
          return;
        }
        params.filter.includeFavorites = {
          condition: {
            path: 'drupal_internal__id',
            operator: 'IN',
            value: this.getFavoritesTypeIds('eventinstance', this.eventType),
          },
        };
      }

      if (this.limit !== 0) {
        params.page = {
          limit: this.limit,
        };
      }

      if (this.featuredLocal) {
        params.filter.field_ls_featured = 1;
      }

      if (this.category) {
        params.filter['eventseries_id.field_ls_category.id'] = this.category;
      }

      params.filter.status = 1;
      params.sort = {
        sortBy: this.sort,
      };

      client
        .get(`jsonapi/eventinstance/${this.eventType}`, { params })
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
    backOneDay() {
      this.date = new Date(this.date.setTime(this.date.getTime() - this.oneDay));
    },
    forwardOneDay() {
      this.date = new Date(this.date.setTime(this.date.getTime() + this.oneDay));
    },
  },
};
</script>
