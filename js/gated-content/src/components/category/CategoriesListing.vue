<template>
  <div v-if="loading" class="text-center">
    <Spinner></Spinner>
  </div>
  <div v-else-if="error" class="text-center">Error loading</div>
  <div v-else-if="showlisting" class="gated-container">
    <h2 class="title" v-if="title !== 'none'">{{ title }}</h2>
    <div class="video-listing category-listing">
      <CategoryTeaser
        v-for="category in listing"
        :key="category.id"
        :category="category"
      />
    </div>
  </div>
</template>

<script>
import client from '@/client';
import CategoryTeaser from '@/components/category/CategoryTeaser.vue';
import Spinner from '@/components/Spinner.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { FavoritesMixin } from '@/mixins/FavoritesMixin';

export default {
  name: 'CategoriesListing',
  mixins: [JsonApiCombineMixin, FavoritesMixin],
  components: {
    CategoryTeaser,
    Spinner,
  },
  props: {
    title: {
      type: String,
      default: 'Categories',
    },
    type: {
      type: String,
      default: 'all',
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
      showlisting: false,
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
    type: 'load',
    bundle: 'load',
    '$route.query': function $routeQuery(newQuery, oldQuery) {
      if (newQuery !== oldQuery) {
        this.load();
      }
    },
  },
  methods: {
    async load() {
      this.showlisting = false;
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

      if (this.favorites) {
        if (this.isFavoritesTypeEmpty('taxonomy_term', 'gc_category')) {
          this.loading = false;
          return;
        }
        params.filter = {};
        params.filter.includeFavorites = {
          condition: {
            path: 'drupal_internal__tid',
            operator: 'IN',
            value: this.getFavoritesTypeIds('taxonomy_term', 'gc_category'),
          },
        };

        client
          .get('jsonapi/taxonomy_term/gc_category', { params })
          .then((response) => {
            this.listing = this.combineMultiple(
              response.data.data,
              response.data.included,
              this.params,
            );
            this.showlisting = true;
            this.loading = false;
          })
          .catch((error) => {
            this.error = true;
            this.loading = false;
            console.error(error);
            throw error;
          });
        return;
      }

      client({
        url: '/api/categories-list',
        method: 'get',
        params: {
          type: this.type,
          bundle: this.bundle,
        },
      })
        .then((response) => {
          params.filter = {};
          if (response.data.length > 0) {
            params.filter.excludeSelf = {
              condition: {
                path: 'id',
                operator: 'IN',
                value: response.data,
              },
            };

            client
              .get('jsonapi/taxonomy_term/gc_category', { params })
              .then((response2) => {
                this.listing = this.combineMultiple(
                  response2.data.data,
                  response2.data.included,
                  this.params,
                );
                this.showlisting = true;
                this.loading = false;
              })
              .catch((error) => {
                this.error = true;
                this.loading = false;
                console.error(error);
                throw error;
              });
          } else {
            this.loading = false;
          }
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
