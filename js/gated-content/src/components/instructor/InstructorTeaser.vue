<template>
  <Teaser
    class="instructor-teaser"
    :routeName="'Instructor'"
    :id="instructor.id"
    :title="instructor.attributes.name"
    :image="instructor.attributes['field_gc_instructor_photo.field_media_image']"
  >
    <template v-slot:outer>
      <AddToFavorite
        :id="instructor.attributes.drupal_internal__tid"
        :type="'taxonomy_term'"
        :bundle="'gc_instructor'"
      />
    </template>
  </Teaser>
</template>

<script>
import Teaser from '@/components/Teaser.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';

export default {
  name: 'InstructorTeaser',
  components: {
    Teaser,
    AddToFavorite,
  },
  props: {
    instructor: {
      type: Object,
      required: true,
    },
  },
};
</script>
