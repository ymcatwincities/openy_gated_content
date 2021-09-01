<template>
  <Teaser
    class="category-teaser"
    :routeName="'Category'"
    :id="category.id"
    :title="category.attributes.name"
    :image="category.attributes['field_gc_category_media.field_media_image']"
  >
    <template>
      <div class="sub-categories" v-if="subcategories.length > 0">
        <div class="sub-categories__title">Includes:</div>
        <ul class="sub-categories__items">
          <li
            v-for="subcategory in subcategories.slice(0, 3)"
            class="sub-categories__item"
            :key="subcategory.tid"
          >{{ subcategory.label }}</li>
          <li v-if="subcategories.length > 3" class="sub-categories__item">more...</li>
        </ul>
      </div>
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
  computed: {
    subcategories() {
      return this.$store.getters.getSubcategories(this.category.attributes.drupal_internal__tid);
    },
  },
};
</script>
