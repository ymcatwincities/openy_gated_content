<template>
  <div class="gated-containerV2 my-40-20 px--20-10">
    <div v-if="loading || listingIsNotEmpty" class="listing-header">
      <h2 class="title text-gray cachet-book-24-20" v-if="title !== 'none'">{{ title }}</h2>
      <slot name="filterButton"></slot>
    </div>
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error" class="text-center">Error loading</div>
    <div v-else class="four-columns">
      <CategoryTeaser
        v-for="category in listing"
        :key="category.id"
        :category="category"
      />
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import client from '@/client';
import CategoryTeaser from '@/components/category/CategoryTeaser.vue';
import Spinner from '@/components/Spinner.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { FavoritesMixin } from '@/mixins/FavoritesMixin';
import { ListingMixin } from '@/mixins/ListingMixin';

export default {
  name: 'CategoriesListing',
  mixins: [JsonApiCombineMixin, FavoritesMixin, ListingMixin],
  components: {
    CategoryTeaser,
    Spinner,
  },
  props: {
    title: {
      type: String,
      default: 'Categories',
    },
    parent: {
      type: String,
      default: null,
    },
    bundle: {
      type: String,
      default: '',
    },
    sort: {
      type: Object,
      default() {
        return { path: 'weight', direction: 'ASC' };
      },
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
      params: [
        'field_gc_category_media',
        // Sub-relationship should be after parent field.
        // @see JsonApiCombineMixin
        'field_gc_category_media.field_media_image',
      ],
    };
  },
  async mounted() {
    await this.load();
  },
  watch: {
    sort: 'load',
    limit: 'load',
    bundle: 'load',
    '$route.query': function $routeQuery(newQuery, oldQuery) {
      if (newQuery !== oldQuery) {
        this.load();
      }
    },
    isCategoriesLoaded: 'load',
  },
  computed: {
    ...mapGetters([
      'isCategoriesLoaded',
      'getCategoriesTree',
    ]),
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

      if (this.limit !== 0) {
        params.page = {
          limit: this.limit,
        };
      }

      params.filter = {};
      if (this.favorites) {
        if (this.isFavoritesTypeEmpty('taxonomy_term', 'gc_category')) {
          this.loading = false;
          return;
        }
        params.filter.includeFavorites = {
          condition: {
            path: 'drupal_internal__tid',
            operator: 'IN',
            value: this.getFavoritesTypeIds('taxonomy_term', 'gc_category'),
          },
        };

        this.loadFromJsonApi(params);
        return;
      }

      if (!this.isCategoriesLoaded) {
        return;
      }
      let categories = this.getCategoriesTree;
      if (this.parent !== null) {
        categories = this.$store.getters.getSubcategories(this.parent);
      }
      if (this.bundle !== '') {
        categories = this.$store.getters.getCategoriesByBundle(this.bundle, categories);
      }
      const tids = categories.map((categoryData) => categoryData.tid);
      if (tids.length === 0) {
        this.loading = false;
        return;
      }
      params.filter.in = {
        condition: {
          path: 'drupal_internal__tid',
          operator: 'IN',
          value: tids,
        },
      };
      this.loadFromJsonApi(params);
    },
    loadFromJsonApi(params) {
      client
        .get('jsonapi/taxonomy_term/gc_category', { params })
        .then((response2) => {
          this.listing = this.combineMultiple(
            response2.data.data,
            response2.data.included,
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
