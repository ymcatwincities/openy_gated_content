<template>
  <div class="gated-content-videos-page">
    <Modal v-if="showModal" @close="showModal = false" class="adjust-modal">
      <template v-slot:header>
        <h3>Filter</h3>
      </template>
      <template v-slot:body>
        <div class="filter">
          <h4>Categories</h4>
          <div class="form-check" v-for="option in contentTypeOptions" :key="option.value">
            <label :for="option.value">
              <input
                type="radio"
                :id="option.value"
                :value="option.value"
                autocomplete="off"
                v-model="preSelectedComponent"
              >
              <span class="checkmark"></span>
              <span class="caption">{{ option.label }}</span>
            </label>
          </div>
        </div>
        <div class="sort">
          <h4>Sort order</h4>
          <div class="form-check" v-for="option in filterOptions" :key="option.value">
            <label :for="option.value">
              <input
                type="radio"
                :id="option.value"
                :value="option.value"
                autocomplete="off"
                v-model="preSelectedSort"
              >
              <span class="checkmark"></span>
              <span class="caption">{{ option.label }}</span>
            </label>
          </div>
        </div>
      </template>
      <template v-slot:footer>
        <button type="button" class="btn btn-outline-primary" @click="showModal = false">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" @click="applyFilters">Apply</button>
      </template>
    </Modal>

    <div class="gated-containerV2 mt-40-20 pb-20-10 px--20-10 title-wrapper">
      <div class="title cachet-book-32-28">Blogs</div>
      <button type="button"
              class="adjust-button" @click="showModal = true">Filter</button>
    </div>

    <BlogListing
      :title="'none'"
      :pagination="true"
      :category="selectedComponent === 'all' ? '' : selectedComponent"
      :sort="filterQuery[selectedSort]"
      class="mb-40-20"
    />
  </div>
</template>

<script>
import client from '@/client';
import BlogListing from '@/components/blog/BlogListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';

export default {
  name: 'BlogsListingPage',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: { BlogListing },
  data() {
    return {
      defaultTypeOptions: [
        { value: 'all', label: 'Show All' },
      ],
      contentTypeOptions: [],
    };
  },
  watch: {
    '$route.query': function $routeQuery(newQuery, oldQuery) {
      if (newQuery !== oldQuery) {
        this.selectedComponent = newQuery.type ? newQuery.type : 'all';
        this.preSelectedComponent = newQuery.type ? newQuery.type : 'all';
        this.selectedSort = newQuery.sort ? newQuery.sort : 'date_desc';
        this.preSelectedSort = newQuery.sort ? newQuery.sort : 'date_desc';
        this.$forceUpdate();
      }
    },
  },
  created() {
    if (this.$route.query.type) {
      this.selectedComponent = this.$route.query.type;
      this.preSelectedComponent = this.$route.query.type;
    }
    if (this.$route.query.sort) {
      this.selectedSort = this.$route.query.sort;
      this.preSelectedSort = this.$route.query.sort;
    }
    this.loadCategories();
  },
  methods: {
    loadCategories() {
      const params = {
        sort: { sortBy: { path: 'name', direction: 'ASC' } },
      };
      client({
        url: '/api/categories-list',
        method: 'get',
        params: { type: 'node', bundle: 'vy_blog_post' },
      })
        .then((response) => {
          params.filter = {};
          if (response.data.length > 0) {
            params.filter.excludeSelf = {
              condition: { path: 'id', operator: 'IN', value: response.data },
            };

            client
              .get('jsonapi/taxonomy_term/gc_category', { params })
              .then((response2) => {
                if (response2.data.data) {
                  const options = [];
                  response2.data.data.forEach((category) => {
                    options.push({
                      value: category.id,
                      label: category.attributes.name,
                    });
                  });
                  this.contentTypeOptions = [...this.defaultTypeOptions, ...options];
                }
              })
              .catch((error) => {
                console.error(error);
              });
          }
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },
};
</script>
