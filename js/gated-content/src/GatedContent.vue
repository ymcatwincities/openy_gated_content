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
  </div>
</template>

<script>
import LogoutLink from '@/components/LogoutLink.vue';
import Spinner from '@/components/Spinner.vue';
import { mapGetters } from 'vuex';

export default {
  name: 'GatedContent',
  props: {
    settings: String,
    headline: String,
    appUrl: {
      type: String,
      default: '',
    },
  },
  components: {
    LogoutLink,
    Spinner,
  },
  computed: {
    ...mapGetters([
      'getAppSettings',
    ]),
  },
  async mounted() {
    await this.$store.dispatch('setSettings', JSON.parse(this.settings));
    await this.$store.dispatch('setHeadline', JSON.parse(this.headline));
    await this.$store.dispatch('loadFavorites');
  },
};
</script>

<style lang="scss">
.spinner {
  justify-content: center;
}
</style>
