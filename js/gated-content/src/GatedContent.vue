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
