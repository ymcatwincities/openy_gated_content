<template>
  <div class="gated-content-videos-page">
    <Modal v-if="showModal" @close="showModal = false" class="adjust-modal">
      <template v-slot:header>
        <h3>Filter</h3>
      </template>
      <template v-slot:body>
        <div class="filter">
          <h4>Categories</h4>
          <div class="form-check" v-for="option in contentTypeOptions" v-bind:key="option.value">
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
          <div class="form-check" v-for="option in filterOptions" v-bind:key="option.value">
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

    <div class="gated-containerV2 my-40-20 px--20-10 title-wrapper">
      <div class="title cachet-book-32-28 text-gray">Videos</div>
      <button type="button"
              class="adjust-button" @click="showModal = true">Filter</button>
    </div>

    <VideoListing
      :title="'none'"
      :pagination="true"
      :category="selectedComponent === 'all' ? '' : selectedComponent"
      :sort="sortData('node', 'gc_video')"
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
  created() {
    this.initSelectedFilters();
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
