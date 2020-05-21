<template>
  <div id="gated-content">
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'GatedContent',
  props: ['auth'],
  mounted() {
    this.$store.dispatch('setAuthPlugin', this.auth);
  },
};
</script>
