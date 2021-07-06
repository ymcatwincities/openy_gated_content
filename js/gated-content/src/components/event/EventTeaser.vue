<template>
  <Teaser
    class="event-teaser"
    :routeName="route"
    :id="video.id"
    :component="type"
    :image="image"
  >
    <template>
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
        v-if="this.video.attributes.host_name"
      >
        <SvgIcon icon="instructor-icon"></SvgIcon>
        {{ this.video.attributes.host_name }}
      </div>
      <div
        class="level"
        v-if="level"
      >
        <SvgIcon icon="difficulty-icon-grey" :css-fill="false"></SvgIcon>
        {{ level.name | capitalize }}
      </div>
      <div class="timer" :class="{live: isOnAir}">
        <template v-if="isOnAir">
          LIVE!
        </template>
        <template v-else>
          Starts in {{ startsIn }}
        </template>
      </div>
    </template>
    <template v-slot:outer>
      <AddToFavorite
        :id="video.attributes.drupal_internal__id"
        :type="'eventinstance'"
        :bundle="type"
      />
    </template>
  </Teaser>
</template>

<script>
import Teaser from '@/components/Teaser.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';
import SvgIcon from '@/components/SvgIcon.vue';
import { EventMixin } from '@/mixins/EventMixin';

export default {
  name: 'EventTeaser',
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
    date() {
      return this.$dayjs.date(this.video.attributes.date.value).format('YYYY-MM-DD');
    },
    image() {
      return this.video.attributes['field_ls_image.field_media_image']
        ?? this.video.attributes['image.field_media_image'];
    },
    level() {
      return this.video.attributes.field_ls_level ?? this.video.attributes.level;
    },
    route() {
      switch (this.video.type) {
        case 'eventinstance--live_stream':
          return 'LiveStream';
        case 'eventinstance--virtual_meeting':
          return 'VirtualMeeting';
        default:
          return 'LiveStream';
      }
    },
    type() {
      return this.video.type.replace('eventinstance--', '');
    },
  },
};
</script>
