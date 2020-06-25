<template>
  <div class="live-stream-listing-page">
    <LiveStreamListing :withDateFilter="true"/>
  </div>
</template>

<script>
import LiveStreamListing from '@/components/event/LiveStreamListing.vue';

export default {
  name: 'LiveStreamListingPage',
  components: {
    LiveStreamListing,
  },
};
</script>
