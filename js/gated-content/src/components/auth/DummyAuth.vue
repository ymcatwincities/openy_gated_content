<template>
  <button @click.prevent="dummyLogin">Login!</button>
</template>

<script>
export default {
  name: 'DummyAuth',
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
