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
      this.$store.dispatch('dummyAuthorize');
      this.$router.push({ name: 'Home' });
    },
  },
};
</script>

<style scoped>

</style>
