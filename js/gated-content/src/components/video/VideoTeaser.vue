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
      <div class="timer">
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
import moment from 'moment';
// eslint-disable-next-line no-unused-vars
import momentDurationFormatSetup from 'moment-duration-format';

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
      return moment.duration(this.video.attributes.field_gc_video_duration, 'seconds').format('m [minute]');
    },
  },
};
</script>
