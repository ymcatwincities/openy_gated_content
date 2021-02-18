export const SettingsMixin = {
  computed: {
    config() {
      return this.$store.getters.getAppSettings;
    },
    componentsOrder() {
      return Object.keys(this.config.components)
        .sort((a, b) => this.config.components[a].weight - this.config.components[b].weight);
    },
  },
  methods: {
    showOnCurrentIteration(component, iteration) {
      return (component === iteration);
    },
  },
};
