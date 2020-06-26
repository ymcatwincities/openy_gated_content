<template>
  <div class="home">
    <BlogListing :featured="false" :viewAll="true" :limit="6"/>
    <VideoListing :featured="true" :viewAll="true" :limit="6"/>
    <LiveStreamListing :featured="true" :viewAll="true" :limit="6"/>
  </div>
</template>

<script>
import BlogListing from '@/components/blog/BlogListing.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import LiveStreamListing from '@/components/event/LiveStreamListing.vue';

export default {
  name: 'Home',
  components: {
    BlogListing,
    VideoListing,
    LiveStreamListing,
  },
};
</script>
