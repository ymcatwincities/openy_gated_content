<template>
  <div>
    <button @click.prevent="dummyLogin">Login!</button>
    <pre>config: {{ config }}</pre>
  </div>
</template>

<script>
export default {
  name: 'DummyAuth',
  computed: {
    config() {
      return this.$store.getters.getDummyConfig;
    },
  },
  methods: {
    dummyLogin() {
      this.$store.dispatch('dummyAuthorize').then(() => {
        const { appUrl } = this.$store.state.auth;
        if (appUrl !== undefined && appUrl.length > 0) {
          window.location = appUrl;
        } else {
          this.$router.push({ name: 'Home' }).catch(() => {});
        }
      });
    },
  },
};
</script>

<style scoped>

</style>
