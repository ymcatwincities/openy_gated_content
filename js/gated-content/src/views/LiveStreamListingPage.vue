<template>
  <div class="live-stream-listing-page">
    <EventListing
      :title="config.components.live_stream.title"
      :withDateFilter="true"
    />
  </div>
</template>

<script>
import EventListing from '@/components/event/EventListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'LiveStreamListingPage',
  mixins: [SettingsMixin],
  components: {
    EventListing,
  },
};
</script>
