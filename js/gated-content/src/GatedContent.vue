<template>
  <div>
    <nav class="text-right gated-container" v-if="isLoggedIn">
      <router-link :to="{ name: 'Home' }">Home</router-link> |
      <LogoutLink />
    </nav>
    <router-view/>
  </div>
</template>

<script>
import LogoutLink from '@/components/LogoutLink.vue';
import { mapGetters } from 'vuex';

export default {
  name: 'GatedContent',
  props: {
    auth: String,
    config: String,
    settings: String,
    appUrl: {
      type: String,
      default: '',
    },
  },
  components: {
    LogoutLink,
  },
  computed: {
    ...mapGetters([
      'isLoggedIn',
    ]),
  },
  created() {
    this.$store.dispatch('setAppUrl', this.appUrl);
    if (this.isLoggedIn && this.appUrl !== undefined && this.appUrl.length > 0) {
      window.location = this.appUrl;
    }
  },
  mounted() {
    this.$store.dispatch('setAuthPlugin', this.auth);
    this.$store.dispatch('setSettings', JSON.parse(this.settings));
    this.$store.dispatch(`${this.auth}Configure`, JSON.parse(this.config));
  },
};
</script>
