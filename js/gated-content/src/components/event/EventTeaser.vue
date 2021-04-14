<template>
  <div class="teaser event-teaser">
    <router-link
      :to="{ name: route, params: { id: video.id } }"
      v-bind:class="{
      'live-stream': route === 'LiveStream',
      'virtual-meeting': route === 'VirtualMeeting'
    }"
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
        {{ level | capitalize }}
      </div>
      <div class="timer" :class="{live: isOnAir}">
        <template v-if="isOnAir">
          LIVE!
        </template>
        <template v-else>
          Starts in {{ startsIn }}
        </template>
      </div>
    </router-link>
    <AddToFavorite
      :id="video.attributes.drupal_internal__id"
      :type="'eventinstance'"
      :bundle="type"
    ></AddToFavorite>
  </div>
</template>

<script>
import AddToFavorite from '@/components/AddToFavorite.vue';
import SvgIcon from '@/components/SvgIcon.vue';
import dayjs from 'dayjs';
import { EventMixin } from '@/mixins/EventMixin';

export default {
  name: 'EventTeaser',
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
    date() {
      return this.$dayjs.date(this.video.attributes.date.value).format('YYYY-MM-DD');
    },
    image() {
      if (this.video.attributes['field_ls_image.field_media_image']) {
        return this.video.attributes['field_ls_image.field_media_image']
          .image_style_uri[0].gated_content_teaser;
      }
      if (this.video.attributes['image.field_media_image']) {
        return this.video.attributes['image.field_media_image']
          .image_style_uri[0].gated_content_teaser;
      }

      return null;
    },
    level() {
      return this.video.attributes.field_ls_level ? this.video.attributes.field_ls_level.name
        : this.video.attributes.level.name;
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
