<template>
  <div class="gated-content-videos">
    <h1>Open Y Gated content</h1>
  </div>
</template>

<script>
export default {
  name: 'VideoListing',
  props: {
    msg: String,
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
.gated-content-videos {
  .h1 {
    color: red;
  }
}
</style>
