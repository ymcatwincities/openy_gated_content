<template>
  <Teaser
    class="blog-teaser"
    :routeName="'BlogPage'"
    :id="blog.id"
    :component="'vy_blog_post'"
    :title="blog.attributes.title"
    :image="blog.attributes['field_vy_blog_image.field_media_image']"
  >
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
};
</script>
