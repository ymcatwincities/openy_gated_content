<template>
  <div class="home">
    <BlogListing :featured="false" :viewAll="true" :limit="6"/>
    <VideoListing :featured="true" :viewAll="true" :limit="6"/>
    <EventListing :featured="true" :viewAll="true" :limit="6"/>
    <EventListing
      :title="'Virtual meetings'"
      :featured="true"
      :viewAll="true"
      :limit="6"
      :eventType="'virtual_meeting'"
    />
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
