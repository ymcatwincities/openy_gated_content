<template>
  <div class="live-stream-listing-page">
    <EventListing
      :title="config.components.virtual_meeting.title"
      :eventType="'virtual_meeting'"
      :withDateFilter="true"
      :msg="config.components.virtual_meeting.empty_block_text"
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
