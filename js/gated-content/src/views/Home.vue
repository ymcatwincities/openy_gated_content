<template>
  <div class="home">
    <VideoListing :featured="true" :viewAll="true" :limit="6"/>
    <LiveStreamListing :featured="true" :viewAll="true" :limit="6"/>
  </div>
</template>

<script>
import VideoListing from '@/components/VideoListing.vue';
import LiveStreamListing from '@/components/LiveStreamListing.vue';

export default {
  name: 'Home',
  components: {
    VideoListing,
    LiveStreamListing,
  },
};
</script>
