<template>
  <div class="home">
    <VideoListing />
  </div>
</template>

<script>
import VideoListing from '@/components/VideoListing.vue';

export default {
  name: 'Home',
  components: {
    VideoListing,
  },
};
</script>
