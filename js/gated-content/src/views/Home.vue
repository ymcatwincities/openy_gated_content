<template>
  <div class="home">
    <VideoListing class="videos gated-container" :featured="true" :viewAll="true"/>
    <LiveStreamListing class="videos gated-container"/>
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
