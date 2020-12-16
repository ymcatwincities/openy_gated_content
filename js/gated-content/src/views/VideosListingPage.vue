<template>
  <div class="gated-content-videos-page">
    <Modal v-if="showModal" @close="showModal = false">
      <template v-slot:header>
        <h3>Adjust</h3>
      </template>
      <template v-slot:body>
        <div class="filter">
          <h4>Categories</h4>
          <div class="form-check" v-for="option in contentTypeOptions" v-bind:key="option.value">
            <input
              type="radio"
              :id="option.value"
              :value="option.value"
              autocomplete="off"
              v-model="preSelectedComponent"
            >
            <label :for="option.value">{{ option.label }}</label>
          </div>
        </div>
        <div class="sort">
          <h4>Sort order</h4>
          <div class="form-check" v-for="option in filterOptions" v-bind:key="option.value">
            <input
              type="radio"
              :id="option.value"
              :value="option.value"
              autocomplete="off"
              v-model="preSelectedSort"
            >
            <label :for="option.value">{{ option.label }}</label>
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

    <div class="gated-container title-wrapper">
      <h2 class="title">Videos</h2>
      <button type="button" class="btn btn-light" @click="showModal = true">Adjust</button>
    </div>

    <VideoListing
      :title="'none'"
      :pagination="true"
      :category="selectedComponent === 'all' ? '' : selectedComponent"
      :sort="filterQuery[selectedSort]"
    />
  </div>
</template>

<script>
import client from '@/client';
import VideoListing from '@/components/video/VideoListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';

export default {
  name: 'VideosListingPage',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: { VideoListing },
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
        params: { type: 'node', bundle: 'gc_video' },
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
