import Modal from '@/components/modal/Modal.vue';

export const FilterAndSortMixin = {
  components: {
    Modal,
  },
  data() {
    return {
      showModal: false,
      DEFAULT_SELECT: 'all',
      DEFAULT_SORT: 'date_desc',
      selectedComponent: this.DEFAULT_SELECT,
      preSelectedComponent: this.selectedComponent,
      selectedSort: null,
      preSelectedSort: this.DEFAULT_SORT,
      contentTypeOptions: [
        { value: 'all', label: 'Show All' },
        { value: 'gc_video', label: 'Video' },
        { value: 'live_stream', label: 'Live stream' },
        { value: 'virtual_meeting', label: 'Virtual meeting' },
        { value: 'vy_blog_post', label: 'Blog' },
      ],
      filterOptions: [
        { value: 'date_desc', label: 'By date (New-Old)' },
        { value: 'date_asc', label: 'By date (Old-New)' },
        { value: 'title_asc', label: 'By title (A-Z)' },
        { value: 'title_desc', label: 'By title (Z-A)' },
      ],
      filterQueryByTypes: {
        node: {
          date_desc: { path: 'created', direction: 'DESC' },
          date_asc: { path: 'created', direction: 'ASC' },
          title_asc: { path: 'title', direction: 'ASC' },
          title_desc: { path: 'title', direction: 'DESC' },
        },
        eventinstance: {
          date_desc: { path: 'date.value', direction: 'DESC' },
          date_asc: { path: 'date.value', direction: 'ASC' },
          title_asc: { path: 'eventseries_id.title', direction: 'ASC' },
          title_desc: { path: 'eventseries_id.title', direction: 'DESC' },
        },
        taxonomy_term: {
          date_desc: { path: 'weight', direction: 'DESC' },
          date_asc: { path: 'weight', direction: 'ASC' },
          title_asc: { path: 'name', direction: 'ASC' },
          title_desc: { path: 'name', direction: 'DESC' },
        },
        personal_training: {
          date_desc: { path: 'date.value', direction: 'DESC' },
          date_asc: { path: 'date.value', direction: 'ASC' },
          title_asc: { path: 'title', direction: 'ASC' },
          title_desc: { path: 'title', direction: 'DESC' },
        },
      },
    };
  },
  watch: {
    '$route.query': function $routeQuery(newQuery, oldQuery) {
      if (newQuery !== oldQuery) {
        this.initSelectedFilters();
      }
    },
  },
  created() {
    this.initSelectedFilters();
  },
  methods: {
    initSelectedFilters() {
      this.selectedComponent = this.$route.query.type || this.DEFAULT_SELECT;
      this.preSelectedComponent = this.selectedComponent;
      this.selectedSort = this.$route.query.sort || null;
      this.preSelectedSort = this.selectedSort || this.DEFAULT_SORT;
    },
    applyFilters() {
      this.$router.push({
        query: {
          type: this.preSelectedComponent,
          sort: this.preSelectedSort,
        },
      });
      this.showModal = false;
    },
    sortData(type, contentType = null) {
      let sort = this.selectedSort;
      if (sort === null) {
        sort = contentType ? this.config.components[contentType].default_sort : this.DEFAULT_SORT;
      }
      return this.filterQueryByTypes[type][sort];
    },
  },
};
