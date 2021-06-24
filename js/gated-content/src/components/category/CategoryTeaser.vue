<template>
  <Teaser
    class="category-teaser"
    :routeName="'Category'"
    :id="category.id"
    :image="category.attributes['field_gc_category_media.field_media_image']"
  >
    <template>
      <div class="title verdana-16-14">{{ category.attributes.name }}</div>
    </template>
    <template v-slot:outer>
      <AddToFavorite
        :id="category.attributes.drupal_internal__tid"
        :type="'taxonomy_term'"
        :bundle="'gc_category'"
      />
    </template>
  </Teaser>
</template>

<script>
import Teaser from '@/components/Teaser.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';

export default {
  name: 'CategoryTeaser',
  components: {
    Teaser,
    AddToFavorite,
  },
  props: {
    category: {
      type: Object,
      required: true,
    },
  },
};
</script>
