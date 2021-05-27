<template>
  <Teaser
    class="blog-teaser"
    :routeName="'BlogPage'"
    :id="blog.id"
    :component="'vy_blog_post'"
    :image="image"
  >
    <template>
      <div class="title">{{ blog.attributes.title }}</div>
    </template>
    <template v-slot:outer>
      <AddToFavorite
        :id="blog.attributes.drupal_internal__nid"
        :type="'node'"
        :bundle="'vy_blog_post'"
      />
    </template>
  </Teaser>
</template>

<script>
import Teaser from '@/components/Teaser.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';

export default {
  name: 'BlogTeaser',
  components: {
    Teaser,
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
