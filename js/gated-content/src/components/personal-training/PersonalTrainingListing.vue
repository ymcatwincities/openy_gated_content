<template>
  <div class="gated-containerV2 my-40-20 px--20-10" v-if="listingIsNotEmpty">
    <div class="listing-header">
      <h2 class="title text-gray">{{ title }}</h2>
      <router-link :to="{ name: 'Schedule' }" v-if="viewAll" class="view-all">
        More
      </router-link>
      <slot name="filterButton"></slot>
    </div>
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <template v-else>
      <div v-if="error">Error loading</div>
      <div v-else :class="layoutClass">
        <PersonalTrainingTeaser
          v-for="personalTraining in listing"
          :key="personalTraining.id"
          :video="personalTraining"
        />
      </div>
    </template>
  </div>
</template>

<script>
import client from '@/client';
import Spinner from '@/components/Spinner.vue';
import PersonalTrainingTeaser from '@/components/personal-training/PersonalTrainingTeaser.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { FavoritesMixin } from '@/mixins/FavoritesMixin';
import { ListingMixin } from '@/mixins/ListingMixin';

export default {
  name: 'PersonalTrainingListing',
  mixins: [JsonApiCombineMixin, FavoritesMixin, ListingMixin],
  components: {
    PersonalTrainingTeaser,
    Spinner,
  },
  props: {
    title: {
      type: String,
      default: 'Personal trainings',
    },
    msg: {
      type: String,
      default: 'No personal trainings found.',
    },
    viewAll: {
      type: Boolean,
      default: false,
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
  },
  data() {
    return {
      loading: true,
      error: false,
      links: {},
      params: [
        'instructor_id',
      ],
    };
  },
  watch: {
    $route: 'load',
    sort: 'load',
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

      params.filter = {
        dateFilter: {
          condition: {
            path: 'date.end_value',
            operator: '>=',
            value: new Date().toISOString(),
          },
        },
        orGroup: {
          group: {
            conjunction: 'OR',
          },
        },
        userFilter1: {
          condition: {
            path: 'customer_id.drupal_internal__uid',
            operator: '=',
            value: window.drupalSettings.user.uid,
            memberOf: 'orGroup',
          },
        },
        userFilter2: {
          condition: {
            path: 'instructor_id.drupal_internal__uid',
            operator: '=',
            value: window.drupalSettings.user.uid,
            memberOf: 'orGroup',
          },
        },
      };

      params.sort = {
        sortBy: this.sort,
      };

      if (this.favorites) {
        if (this.isFavoritesTypeEmpty('personal_training', 'personal_training')) {
          this.loading = false;
          return;
        }
        params.filter.includeFavorites = {
          condition: {
            path: 'drupal_internal__id',
            operator: 'IN',
            value: this.getFavoritesTypeIds('personal_training', 'personal_training'),
          },
        };
      }

      if (this.limit !== 0) {
        params.page = {
          limit: this.limit,
        };
      }

      client
        .get('jsonapi/personal_training/personal_training', { params })
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
