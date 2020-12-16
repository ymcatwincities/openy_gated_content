<template>
  <div class="teaser blog-teaser">
    <router-link
      :to="{ name: 'BlogPage', params: { id: blog.id } }">
      <div class="title">{{ blog.attributes.title }}</div>
    </router-link>
    <AddToFavorite
      :id="blog.attributes.drupal_internal__nid"
      :type="'node'"
      :bundle="'vy_blog_post'"
    ></AddToFavorite>
  </div>
</template>

<script>
import AddToFavorite from '@/components/AddToFavorite.vue';

export default {
  name: 'BlogTeaser',
  components: {
    AddToFavorite,
  },
  props: {
    blog: {
      type: Object,
      required: true,
    },
  },
  computed: {
    image() {
      if (!this.blog.attributes['field_vy_blog_image.field_media_image']) {
        return null;
      }

      return this.blog.attributes['field_vy_blog_image.field_media_image']
        .image_style_uri[0].gated_content_teaser;
    },
  },
};
</script>
