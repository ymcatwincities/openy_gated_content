<template>
  <div class="gated-content-categories-page">
    <Modal v-if="showModal" @close="showModal = false" class="adjust-modal">
      <template v-slot:header>
        <h3>Filter</h3>
      </template>
      <template v-slot:body>
        <div class="filter">
          <h4>Content types</h4>
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
      <div class="title cachet-book-32-28 text-gray">{{ title }}</div>
      <button type="button"
              class="adjust-button" @click="showModal = true">Filter</button>
    </div>

    <CategoriesListing
      :title="'none'"
      :type="selectedType"
      :bundle="selectedBundle"
      :sort="filterQuery[selectedSort]"
      :limit="50"
    />
  </div>
</template>

<script>
import CategoriesListing from '@/components/category/CategoriesListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';

export default {
  name: 'CategoriesListingPage',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: {
    CategoriesListing,
  },
  data() {
    return {
      DEFAULT_SORT: 'weight_asc',
      filterOptions: [
        { value: 'title_asc', label: 'By title (A-Z)' },
        { value: 'title_desc', label: 'By title (Z-A)' },
      ],
      filterQuery: {
        weight_asc: { path: 'weight', direction: 'ASC' },
        weight_desc: { path: 'weight', direction: 'DESC' },
        title_asc: { path: 'name', direction: 'ASC' },
        title_desc: { path: 'name', direction: 'DESC' },
      },
    };
  },
  computed: {
    selectedType() {
      switch (this.selectedComponent) {
        case 'gc_video':
        case 'vy_blog_post':
          return 'node';
        case 'live_stream':
        case 'virtual_meeting':
          return 'eventinstance';
        default:
          return 'all';
      }
    },
    selectedBundle() {
      return this.selectedComponent === 'all' ? '' : this.selectedComponent;
    },
    title() {
      if (this.selectedComponent === 'all' || typeof this.config.components[this.selectedComponent] === 'undefined') {
        return 'Categories';
      }

      return `${this.config.components[this.selectedComponent].title} Categories`;
    },
  },
  watch: {
    '$route.query': function $routeQuery(newQuery, oldQuery) {
      if (newQuery !== oldQuery) {
        this.selectedComponent = newQuery.type ? newQuery.type : 'all';
        this.preSelectedComponent = newQuery.type ? newQuery.type : 'all';
        this.selectedSort = newQuery.sort ? newQuery.sort : 'weight_asc';
        this.preSelectedSort = newQuery.sort ? newQuery.sort : 'weight_asc';
        this.$forceUpdate();
      }
    },
  },
};
</script>
