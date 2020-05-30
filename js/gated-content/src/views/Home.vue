<template>
  <div class="home">
    <LogoutLink />
    <VideoListing class="videos" />
    <LiveStreamListing class="videos" />
  </div>
</template>

<script>
import LogoutLink from '@/components/LogoutLink.vue';
import VideoListing from '@/components/VideoListing.vue';
import LiveStreamListing from '@/components/LiveStreamListing.vue';

export default {
  name: 'Home',
  components: {
    LogoutLink,
    VideoListing,
    LiveStreamListing,
  },
};
</script>
