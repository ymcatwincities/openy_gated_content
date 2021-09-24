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
    instructors() {
      return this.video.attributes.field_gc_instructor_reference.length > 0
        ? this.video.attributes.field_gc_instructor_reference
        : this.video.attributes.instructor_reference;
    },
  },
  methods: {
    multipleReferencesWorkaround(response) {
      // We need here small hack for equipment.
      // In included we have all referenced items, but in relationship only one.
      // So we need manually pass this items to this.video.attributes.equipment.
      this.video.attributes.equipment = [];
      this.video.attributes.category = [];
      this.video.attributes.instructor_reference = [];
      if (response.data.included.length > 0) {
        response.data.included.forEach((ref) => {
          if (ref.type === 'taxonomy_term--gc_equipment') {
            this.video.attributes.equipment.push(ref.attributes);
          }
          if (ref.type === 'taxonomy_term--gc_category') {
            this.video.attributes.category.push(ref.attributes);
          }
          if (ref.type === 'taxonomy_term--gc_instructor') {
            this.video.attributes.instructor_reference.push({ ...ref.attributes, uuid: ref.id });
          }
        });
      }
    },
  },
};
