export const SeriesEventMixin = {
  watch: {
    $route: 'load',
  },
  async mounted() {
    await this.load();
  },
  computed: {
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
        : this.video.attributes.host_name;
    },
  },
};
