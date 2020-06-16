<template>
  <div class="live-stream-listing-page">
    <LiveStreamListing :withDateFilter="true"/>
  </div>
</template>

<script>
import LiveStreamListing from '@/components/LiveStreamListing.vue';

export default {
  name: 'LiveStreamListingPage',
  components: {
    LiveStreamListing,
  },
};
</script>

<style lang="scss">
</style>
