<template>
  <div class="gated-containerV2 my-40-20 px--20-10">
    <div v-if="loading || listingIsNotEmpty" class="listing-header">
      <h2 class="title text-gray cachet-book-24-20" v-if="title !== 'none'">{{ title }}</h2>
      <template v-if="hasMoreItems">
        <router-link :to="{ name: 'DurationsListingPage' }" v-if="viewAll" class="view-all">
          More
        </router-link>
        <slot name="filterButton"></slot>
      </template>
    </div>
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error" class="text-center">Error loading</div>
    <div v-else-if="listingIsNotEmpty">
      <div class="four-columns">
        <DurationTeaser
            v-for="duration in listing"
            :key="duration.id"
            :duration="duration"
        />
      </div>
    </div>
    <div v-else class="empty-listing">
      {{ emptyBlockMsg }}
    </div>
  </div>
</template>

<script>
import client from '@/client';
import DurationTeaser from '@/components/duration/DurationTeaser.vue';
import Spinner from '@/components/Spinner.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { FavoritesMixin } from '@/mixins/FavoritesMixin';
import { ListingMixin } from '@/mixins/ListingMixin';

export default {
  name: 'DurationsListing',
  mixins: [JsonApiCombineMixin, FavoritesMixin, ListingMixin],
  components: {
    DurationTeaser,
    Spinner,
  },
  props: {
    title: {
      type: String,
      default: 'Duration',
    },
    sort: {
      type: Object,
      default() {
        return { path: 'weight', direction: 'ASC' };
      },
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      listing: null,
      params: [
        'field_gc_duration_media',
        // Sub-relationship should be after parent field.
        // @see JsonApiCombineMixin
        'field_gc_duration_media.field_media_image',
      ],
    };
  },
  async mounted() {
    await this.load();
  },
  watch: {
    sort: 'load',
    limit: 'load',
    '$route.query': function $routeQuery(newQuery, oldQuery) {
      if (newQuery !== oldQuery) {
        this.load();
      }
    },
  },
  methods: {
    async load() {
      this.listing = [];
      this.loading = true;
      const params = {};
      if (this.params) {
        params.include = this.params.join(',');
      }

      params.sort = {
        sortBy: this.sort,
      };

      params.page = this.getPageParam;

      params.filter = {};

      if (this.favorites) {
        if (this.isFavoritesTypeEmpty('taxonomy_term', 'gc_duration')) {
          this.loading = false;
          return;
        }
        params.filter.includeFavorites = {
          condition: {
            path: 'drupal_internal__tid',
            operator: 'IN',
            value: this.getFavoritesTypeIds('taxonomy_term', 'gc_duration'),
          },
        };
      }

      this.loadFromJsonApi(params);
    },
    loadFromJsonApi(params) {
      client
        .get('jsonapi/taxonomy_term/gc_duration', { params })
        .then((response) => {
          this.links = response.data.links;
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
