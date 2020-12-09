<template>
  <div>
    <TopMenu></TopMenu>
    <div v-if="!getAppSettings" class="text-center">
      <Spinner></Spinner>
    </div>
    <router-view v-else/>
  </div>
</template>

<script>
import Spinner from '@/components/Spinner.vue';
import TopMenu from '@/components/TopMenu.vue';
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
    TopMenu,
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
