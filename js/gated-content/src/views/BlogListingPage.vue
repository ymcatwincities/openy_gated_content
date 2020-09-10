<template>
  <div class="blog-listing-page">
    <BlogListing
      :title="config.components.vy_blog_post.title"
      :pagination="true"
    />
  </div>
</template>

<script>
import BlogListing from '@/components/blog/BlogListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'BlogListingPage',
  mixins: [SettingsMixin],
  components: {
    BlogListing,
  },
};
</script>
