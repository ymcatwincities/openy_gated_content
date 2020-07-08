<template>
  <div class="live-stream-listing-page">
    <EventListing
      :withDateFilter="true"
      :msg="'No Live streams scheduled for this date.'"
    />
  </div>
</template>

<script>
import EventListing from '@/components/event/EventListing.vue';

export default {
  name: 'LiveStreamListingPage',
  components: {
    EventListing,
  },
};
</script>
