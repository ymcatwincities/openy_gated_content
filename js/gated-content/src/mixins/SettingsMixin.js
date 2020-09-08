export const SettingsMixin = {
  computed: {
    config() {
      return this.$store.getters.getAppSettings;
    },
  },
};
