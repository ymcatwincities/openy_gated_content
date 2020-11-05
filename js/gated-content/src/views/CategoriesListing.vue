<template>
  <div v-if="showlisting" class="gated-container">
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
  name: 'CategoriesListing',
  mixins: [JsonApiCombineMixin],
  components: {
    CategoryTeaser,
    Spinner,
  },
  props: {
    type: {
      type: String,
      required: true,
      validator(value) {
        // Can be video or blog.
        return ['video', 'blog'].indexOf(value) !== -1;
      },
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
  computed: {
    title() {
      return this.type === 'video' ? 'Video categories' : 'Blog categories';
    },
  },
  watch: {
    'type': function() {
      this.load();
    }
  },
  methods: {
    listingIsNotEmpty() {
      if(this.listing !== null && this.listing.length > 0) {
        this.showlisting = true;
      } else {
        this.showlisting = false;
      }
    },
    async load() {
      this.showlisting = false;
      const params = {};
      const bundle = this.type === 'video' ? 'gc_video' : 'vy_blog_post';
      if (this.params) {
        params.include = this.params.join(',');
      }

      client
        .get(`api/video-categories-list/${bundle}`, { params })
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
