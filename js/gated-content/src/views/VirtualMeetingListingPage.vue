<template>
  <div class="live-stream-listing-page">
    <EventListing
      :title="config.components.virtual_meeting.title"
      :eventType="'virtual_meeting'"
      :withDateFilter="true"
      :msg="'No Virtual Meetings scheduled for this date.'"
    />
  </div>
</template>

<script>
import EventListing from '@/components/event/EventListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'VirtualMeetingListingPage',
  mixins: [SettingsMixin],
  components: {
    EventListing,
  },
};
</script>
