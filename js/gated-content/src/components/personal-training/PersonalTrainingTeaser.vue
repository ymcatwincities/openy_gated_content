<template>
  <Teaser
    class="event-teaser personal-training-teaser"
    :routeName="'PersonalTraining'"
    :id="video.id"
    :title="video.attributes.title"
  >
    <template>
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
      <div class="timers-wrapper">
        <div class="timer private">
          Private
        </div>
        <div class="timer canceled" v-if="video.attributes.state === 'canceled'">
          Canceled
        </div>
      </div>
    </template>
    <template v-slot:outer>
      <AddToFavorite
        :id="video.attributes.drupal_internal__id"
        :type="'personal_training'"
        :bundle="'personal_training'"
      ></AddToFavorite>
    </template>
  </Teaser>
</template>

<script>
import Teaser from '@/components/Teaser.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';
import SvgIcon from '@/components/SvgIcon.vue';
import { EventMixin } from '@/mixins/EventMixin';

export default {
  name: 'PersonalTrainingTeaser',
  mixins: [EventMixin],
  components: {
    Teaser,
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
