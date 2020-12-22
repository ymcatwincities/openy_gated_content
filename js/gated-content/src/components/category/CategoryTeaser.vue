<template>
  <div class="teaser category-teaser">
    <router-link :to="{ name: 'Category', params: { cid: category.id } }">
        <div class="preview" v-bind:style="{
              backgroundImage: `url(${image})`
            }"
        v-if="image">
        </div>
        <div class="title verdana-16-14">{{ category.attributes.name }}</div>
    </router-link>
    <AddToFavorite
      :id="category.attributes.drupal_internal__tid"
      :type="'taxonomy_term'"
      :bundle="'gc_category'"
    ></AddToFavorite>
  </div>
</template>

<script>
import AddToFavorite from '@/components/AddToFavorite.vue';

export default {
  name: 'CategoryTeaser',
  components: {
    AddToFavorite,
  },
  props: {
    category: {
      type: Object,
      required: true,
    },
  },
  computed: {
    image() {
      if (!this.category.attributes['field_gc_category_media.field_media_image']) {
        return null;
      }

      return this.category.attributes['field_gc_category_media.field_media_image']
        .image_style_uri[0].gated_content_teaser;
    },
  },
};
</script>
