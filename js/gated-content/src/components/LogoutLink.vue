<template>
  <span class="menu">
    <a :href="logoutUrl">Sign out</a>
  </span>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  name: 'LogoutLink',
  computed: {
    ...mapGetters([
      'getAppSettings',
    ]),
    logoutUrl() {
      if (this.getAppSettings !== null) {
        return this.getAppSettings.virtual_y_logout_url;
      }
    },
  },
};
</script>
