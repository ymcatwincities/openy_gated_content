<template>
  <div class="home">
    <VideoListing :featured="true" :viewAll="true" :limit="6"/>
    <EventListing
      :featured="true"
      :viewAll="true"
      :limit="6"
      :msg="'Live streams not found.'"
    />
    <EventListing
      :title="'Virtual meetings'"
      :featured="true"
      :viewAll="true"
      :limit="6"
      :eventType="'virtual_meeting'"
      :msg="'Virtual Meetings not found.'"
    />
    <BlogListing :featured="false" :viewAll="true" :limit="6"/>
  </div>
</template>

<script>
import BlogListing from '@/components/blog/BlogListing.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import EventListing from '@/components/event/EventListing.vue';

export default {
  name: 'Home',
  components: {
    BlogListing,
    VideoListing,
    EventListing,
  },
};
</script>
