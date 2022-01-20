<template>
  <Teaser
    class="duration-teaser"
    :routeName="'Duration'"
    :id="duration.id"
    :title="duration.attributes.name"
    :image="duration.attributes['field_gc_duration_media.field_media_image']"
  >
    <template v-slot:outer>
      <AddToFavorite
        :id="duration.attributes.drupal_internal__tid"
        :type="'taxonomy_term'"
        :bundle="'gc_duration'"
      />
    </template>
  </Teaser>
</template>

<script>
import Teaser from '@/components/Teaser.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';

export default {
  name: 'DurationTeaser',
  components: {
    Teaser,
    AddToFavorite,
  },
  props: {
    duration: {
      type: Object,
      required: true,
    },
  },
};
</script>
