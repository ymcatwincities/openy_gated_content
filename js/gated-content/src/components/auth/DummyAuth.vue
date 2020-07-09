<template>
  <div>
    <div v-if="loading" class="spinner-center">
      <Spinner></Spinner>
    </div>
    <template v-else>
      <button class="gc-button" @click.prevent="dummyLogin">Enter Virtual YMCA</button>
    </template>
  </div>
</template>

<script>
import Spinner from '@/components/Spinner.vue';

export default {
  name: 'DummyAuth',
  components: {
    Spinner,
  },
  data() {
    return {
      loading: false,
    };
  },
  computed: {
    config() {
      return this.$store.getters.getDummyConfig;
    },
  },
  methods: {
    dummyLogin() {
      this.$store.dispatch('dummyAuthorize').then(() => {
        const appUrl = this.$store.getters.getAppUrl;
        if (appUrl !== undefined && appUrl.length > 0) {
          this.loading = true;
          window.location = appUrl;
        } else {
          this.$router.push({ name: 'Home' }).catch(() => {});
        }
      }).catch(() => {});
    },
  },
};
</script>

<style scoped>

</style>
