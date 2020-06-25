<template>
  <div v-if="listingIsNotEmpty" class="gated-container">
    <h2 class="title">{{ title }}</h2>
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <div v-else class="video-listing category-listing">
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
import CategoryTeaser from '@/components/video/CategoryTeaser.vue';
import Spinner from '@/components/Spinner.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';

export default {
  name: 'VideoCategoriesListing',
  mixins: [JsonApiCombineMixin],
  components: {
    CategoryTeaser,
    Spinner,
  },
  props: {
    title: {
      type: String,
      default: 'Categories',
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

      client
        .get('jsonapi/taxonomy_term/gc_category', { params })
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
