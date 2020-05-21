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

<style lang="scss">
.gated-content-videos {
  h1 {
    color: red;
  }
}
</style>
