export const EventMixin = {
  watch: {
    $route: 'load',
  },
  async mounted() {
    await this.load();
  },
  computed: {
    pageUrl() {
      return window.location.href;
    },
    // This values most of all from parent (series), but can be overridden by item,
    // so ve need to check this here and use correct value.
    description() {
      return this.video.attributes.body ? this.video.attributes.body
        : this.video.attributes.description;
    },
    level() {
      return this.video.attributes.field_ls_level ? this.video.attributes.field_ls_level.name
        : this.video.attributes.level.name;
    },
    category() {
      return this.video.attributes.field_ls_category.length > 0
        ? this.video.attributes.field_ls_category
        : this.video.attributes.category;
    },
    instructor() {
      return this.video.attributes.field_ls_host_name ? this.video.attributes.field_ls_host_name
        : this.video.attributes.instructor;
    },
    config() {
      return this.$store.getters.getAppSettings;
    },
  },
  methods: {
    formatDate: (date) => {
      if (!date) return '';
      const dateObj = new Date(date);
      return dateObj.toISOString();
    },
    getDuration: (date) => {
      if (!date) return '';
      const dateStart = new Date(date.value);
      const dateEnd = new Date(date.end_value);
      const Diff = Math.abs(dateEnd.getTime() - dateStart.getTime());
      // Diff in hours.
      return Diff / 1000 / 3600;
    },
  },
};
