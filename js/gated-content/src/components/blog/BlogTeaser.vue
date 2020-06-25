<template>
  <div class="blog-teaser category-teaser">
    <router-link :to="{ name: 'BlogPage', params: { id: blog.id } }">
        <div class="preview" v-bind:style="{
              backgroundImage: `url(${image})`
            }"
        v-if="image">
        </div>
        <div class="title">{{ blog.attributes.title }}</div>
    </router-link>
  </div>
</template>

<script>
export default {
  name: 'BlogTeaser',
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
