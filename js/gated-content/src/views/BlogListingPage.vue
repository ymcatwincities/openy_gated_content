<template>
  <div class="blog-listing-page">
    <BlogListing/>
  </div>
</template>

<script>
import BlogListing from '@/components/blog/BlogListing.vue';

export default {
  name: 'BlogListingPage',
  components: {
    BlogListing,
  },
};
</script>
