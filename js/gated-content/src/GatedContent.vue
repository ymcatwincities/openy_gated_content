<template>
  <div id="gated-content">
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'GatedContent',
  props: ['auth', 'config'],
  mounted() {
    this.$store.dispatch('setAuthPlugin', this.auth);
    this.$store.dispatch(`${this.auth}Configure`, JSON.parse(this.config));
  },
};
</script>
