<template>
  <div>
    <nav class="text-right gated-container">
      <router-link :to="{ name: 'Home' }">Home</router-link> |
      <LogoutLink />
    </nav>
    <div v-if="!getAppSettings" class="text-center">
      <Spinner></Spinner>
    </div>
    <router-view v-else/>
    <ScrollToTop></ScrollToTop>
  </div>
</template>

<script>
import LogoutLink from '@/components/LogoutLink.vue';
import Spinner from '@/components/Spinner.vue';
import ScrollToTop from '@/components/ScrollToTop.vue';
import { mapGetters } from 'vuex';

export default {
  name: 'GatedContent',
  props: {
    settings: String,
    appUrl: {
      type: String,
      default: '',
    },
  },
  components: {
    LogoutLink,
    Spinner,
    ScrollToTop,
  },
  computed: {
    ...mapGetters([
      'getAppSettings',
    ]),
  },
  created() {
    this.$store.dispatch('setAppUrl', this.appUrl);
    if (this.appUrl !== undefined && this.appUrl.length > 0) {
      window.location = this.appUrl;
    }
  },
  mounted() {
    this.$store.dispatch('setSettings', JSON.parse(this.settings));
  },
};
</script>

<style lang="scss">
.spinner {
  justify-content: center;
}
</style>
