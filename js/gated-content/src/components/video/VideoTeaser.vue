<template>
  <div class="teaser video-teaser">
    <router-link
      :to="{ name: 'Video', params: { id: video.id } }">
      <div class="preview" v-bind:style="{
              backgroundImage: `url(${image})`
            }">
        <div class="play-button"></div>
      </div>
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
    </router-link>
    <AddToFavorite
      :id="video.attributes.drupal_internal__nid"
      :type="'node'"
      :bundle="'gc_video'"
      class="white"
    ></AddToFavorite>
  </div>
</template>

<script>
import AddToFavorite from '@/components/AddToFavorite.vue';
import SvgIcon from '@/components/SvgIcon.vue';
import dayjs from 'dayjs';

export default {
  name: 'VideoTeaser',
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
    image() {
      if (this.video.attributes['field_gc_video_image.field_media_image']) {
        return this.video.attributes['field_gc_video_image.field_media_image']
          .image_style_uri[0].gated_content_teaser;
      }

      if (!this.video.attributes['field_gc_video_media.thumbnail']) {
        return null;
      }

      return this.video.attributes['field_gc_video_media.thumbnail'].image_style_uri[0].gated_content_teaser;
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
