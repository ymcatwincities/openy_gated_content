<template>
  <div class="live-stream-listing-page">
    <EventListing
      :title="'Virtual meetings'"
      :eventType="'virtual_meeting'"
      :withDateFilter="true"
      :msg="'No Virtual Meetings scheduled for this date.'"
    />
  </div>
</template>

<script>
import EventListing from '@/components/event/EventListing.vue';

export default {
  name: 'VirtualMeetingListingPage',
  components: {
    EventListing,
  },
};
</script>
