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
      :categories="selectedComponent === 'all' ? null : [selectedComponent]"
      :sort="sortData('node', 'vy_blog_post')"
      class="mb-40-20"
    />
  </div>
</template>

<script>
import BlogListing from '@/components/blog/BlogListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';
import { mapGetters } from 'vuex';

export default {
  name: 'BlogsListingPage',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: { BlogListing },
  data() {
    return {
      defaultTypeOptions: { value: 'all', label: 'Show All' },
      contentTypeOptions: [],
    };
  },
  created() {
    this.loadCategories();
    this.initSelectedFilters();
  },
  watch: {
    isCategoriesLoaded: 'loadCategories',
  },
  computed: {
    ...mapGetters([
      'isCategoriesLoaded',
    ]),
  },
  methods: {
    loadCategories() {
      const options = this.$store.getters.getCategoriesByBundle('vy_blog_post')
        .sort((a, b) => {
          if (a.label < b.label) return -1;
          if (a.label > b.label) return 1;
          return 0;
        })
        .map((category) => ({
          value: category.tid,
          label: category.label,
        }));
      this.contentTypeOptions = [this.defaultTypeOptions, ...options];
    },
  },
};
</script>
