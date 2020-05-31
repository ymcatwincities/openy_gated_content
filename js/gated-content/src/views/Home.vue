<template>
  <div class="home">
    <VideoListing class="videos" />
    <LiveStreamListing class="videos" />
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
