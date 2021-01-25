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
      preSelectedComponent: this.DEFAULT_SELECT,
      selectedSort: this.DEFAULT_SORT,
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
      filterQuery: {
        date_desc: { path: 'created', direction: 'DESC' },
        date_asc: { path: 'created', direction: 'ASC' },
        title_asc: { path: 'title', direction: 'ASC' },
        title_desc: { path: 'title', direction: 'DESC' },
      },
    };
  },
  watch: {
    $route: 'initSelectedFilters',
  },
  created() {
    this.initSelectedFilters();
  },
  methods: {
    initSelectedFilters() {
      this.selectedComponent = this.$route.query.type
        ? this.$route.query.type : this.DEFAULT_SELECT;
      this.preSelectedComponent = this.$route.query.type
        ? this.$route.query.type : this.DEFAULT_SELECT;
      this.selectedSort = this.$route.query.sort ? this.$route.query.sort : this.DEFAULT_SORT;
      this.preSelectedSort = this.$route.query.sort ? this.$route.query.sort : this.DEFAULT_SORT;
    },
    applyFilters() {
      this.selectedComponent = this.preSelectedComponent;
      this.selectedSort = this.preSelectedSort;
      this.$router.push({
        query: {
          type: this.selectedComponent,
          sort: this.selectedSort,
        },
      });
      this.showModal = false;
    },
  },
};
