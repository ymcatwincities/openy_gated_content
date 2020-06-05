<template>
  <div class="video-teaser live-stream-teaser">
    <router-link :to="{ name: 'LiveStream', params: { id: video.id } }">
      <div class="preview" v-bind:style="{
              backgroundImage: `url(${image})`
            }">
        <div class="month">
          <p>{{ video.attributes.date.value | month }}</p>
          <p>{{ video.attributes.date.value | day }}</p>
        </div>
      </div>
      <div class="title">{{ video.attributes.title }}</div>
      <div class="meta">
        <div class="schedule">
          <i class="fa fa-clock-o" aria-hidden="true"></i>
          {{ this.video.attributes.date | schedule }}
        </div>
      </div>
      <div v-if="isOnAir" class="controls join">
        <router-link
          :to="{ name: 'LiveStream', params: { id: video.id } }">
          Join live stream
        </router-link>
      </div>
      <div v-else class="controls subscribe">
        <div>
          <div class="video-level">{{ level | first_letter }}</div>{{ level | capitalize }}
        </div>
        <div class="fa fa-calendar-plus-o"></div>
      </div>
    </router-link>
  </div>
</template>

<script>

export default {
  name: 'VideoTeaser',
  props: {
    video: {
      type: Object,
      required: true,
    },
  },
  computed: {
    image() {
      if (this.video.attributes['field_ls_media.thumbnail']) {
        return this.video.attributes['field_ls_media.thumbnail']
          .image_style_uri[0].gated_content_teaser;
      }
      if (this.video.attributes['media.thumbnail']) {
        return this.video.attributes['media.thumbnail']
          .image_style_uri[0].gated_content_teaser;
      }
      else {
        return null;
      }
    },
    level() {
      return this.video.attributes.field_ls_level ? this.video.attributes.field_ls_level.name
        : this.video.attributes.level.name;
    },
    isOnAir() {
      const dateStart = new Date(this.video.attributes.date.value);
      const dateEnd = new Date(this.video.attributes.date.end_value);
      const now = new Date();
      return dateStart < now && now < dateEnd;
    },
  },
};
</script>

<style lang="scss">
</style>
