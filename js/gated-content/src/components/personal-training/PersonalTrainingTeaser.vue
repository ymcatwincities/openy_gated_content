<template>
  <div class="teaser event-teaser personal-training-teaser">
    <router-link
      :to="{ name: 'PersonalTraining', params: { id: video.id } }"
    >
      <div class="title">{{ video.attributes.title }}</div>
      <div class="date">
        <SvgIcon icon="date-icon"></SvgIcon>
        {{ date }}
      </div>
      <div class="time">
        <SvgIcon icon="clock-regular"></SvgIcon>
        {{ time }} ({{ duration }})
      </div>
      <div
        class="instructor"
        v-if="instructor"
      >
        <SvgIcon icon="instructor-icon"></SvgIcon>
        {{ instructor }}
      </div>
      <div class="timer">
        Private
      </div>
    </router-link>
    <AddToFavorite
      :id="video.attributes.drupal_internal__id"
      :type="'personal_training'"
      :bundle="'personal_training'"
    ></AddToFavorite>
  </div>
</template>

<script>
import AddToFavorite from '@/components/AddToFavorite.vue';
import SvgIcon from '@/components/SvgIcon.vue';
import { EventMixin } from '@/mixins/EventMixin';

export default {
  name: 'PersonalTrainingTeaser',
  mixins: [EventMixin],
  components: {
    SvgIcon,
    AddToFavorite,
  },
  props: {
    video: {
      type: Object,
      required: true,
    },
  },
  computed: {
    instructor() {
      return this.video.attributes.instructor_id ? this.video.attributes.instructor_id.display_name : '';
    },
  },
};
</script>
