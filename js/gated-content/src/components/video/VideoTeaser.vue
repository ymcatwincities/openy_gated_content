<template>
  <Teaser
    class="video-teaser"
    :routeName="'Video'"
    :id="video.id"
    :component="'gc_video'"
    :image="image"
    :title="video.attributes.title"
  >
    <template v-slot:overlay>
      <div class="play-button"></div>
    </template>
    <template>
      <div class="title">{{ video.attributes.title }}</div>
      <div
        class="instructor"
        v-if="this.video.attributes.field_gc_video_instructor"
      >
        <SvgIcon icon="instructor-icon"></SvgIcon>
        {{ this.video.attributes.field_gc_video_instructor }}
      </div>
      <div
        class="level"
        v-if="video.attributes.field_gc_video_level"
      >
        <SvgIcon icon="difficulty-icon-white" :css-fill="false"></SvgIcon>
        {{ video.attributes.field_gc_video_level.name | capitalize }}
      </div>
      <div class="timer" :style="{ visibility: this.video
        .attributes.field_gc_video_duration ? 'visible': 'hidden'}">
        {{ duration }}
      </div>
    </template>
    <template v-slot:outer>
      <AddToFavorite
        :id="video.attributes.drupal_internal__nid"
        :type="'node'"
        :bundle="'gc_video'"
        class="white"
      />
    </template>
  </Teaser>
</template>

<script>
import Teaser from '@/components/Teaser.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';
import SvgIcon from '@/components/SvgIcon.vue';
import dayjs from 'dayjs';

export default {
  name: 'VideoTeaser',
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
    image() {
      return this.video.attributes['field_gc_video_image.field_media_image']
        ?? this.video.attributes['field_gc_video_media.thumbnail'];
    },
    duration() {
      const sec = this.video.attributes.field_gc_video_duration;
      if (sec > 0 && sec < 60) {
        return `${sec} ${this.$options.filters.simplePluralize('second', sec)}`;
      }

      const min = Math.floor(dayjs.duration(sec, 'seconds').asMinutes());
      return `${min} ${this.$options.filters.simplePluralize('minute', min)}`;
    },
  },
};
</script>
