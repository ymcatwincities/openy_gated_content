<template>
  <div class="home">
    <LogoutLink />
    <VideoListing />
  </div>
</template>

<script>
import LogoutLink from '@/components/LogoutLink.vue';
import VideoListing from '@/components/VideoListing.vue';

export default {
  name: 'Home',
  components: {
    LogoutLink,
    VideoListing,
  },
};
</script>
