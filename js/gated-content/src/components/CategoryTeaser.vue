<template>
  <div class="video-teaser">
    <router-link :to="{ name: 'Category', params: { cid: category.id } }">
        <div class="preview" v-bind:style="{
              backgroundImage: `url(${image})`
            }"
        v-if="image">
        </div>
        <div class="title">{{ category.attributes.name }}</div>
    </router-link>
  </div>
</template>

<script>
export default {
  name: 'CategoryTeaser',
  props: {
    category: {
      type: Object,
      required: true,
    },
  },
  computed: {
    image() {
      if (!this.category.attributes.field_media_image) {
        return null;
      }

      return this.category.attributes.field_media_image.image_style_uri[0].gated_content_teaser;
    },
  },
};
</script>

<style lang="scss">

</style>
