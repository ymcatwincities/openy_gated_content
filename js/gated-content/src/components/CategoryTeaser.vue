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
      // const vid = this.category.attributes.field_gc_category_media;
      return this.category.attributes.field_media_image
        ? this.category.attributes.field_media_image.uri.url : null;
    },
  },
};
</script>

<style lang="scss">

</style>
