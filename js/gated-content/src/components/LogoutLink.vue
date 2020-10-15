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
      return this.getAppSettings !== null ? this.getAppSettings.virtual_y_logout_url : '';
    },
  },
};
</script>
