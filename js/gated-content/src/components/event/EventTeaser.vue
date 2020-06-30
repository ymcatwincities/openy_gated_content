<template>
  <div
    class="video-teaser event-teaser"
    v-bind:class="{
      'live-stream': route === 'LiveStream',
      'virtual-meeting': route === 'VirtualMeeting'
    }"
  >
    <router-link :to="{ name: route, params: { id: video.id } }">
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
          :to="{ name: route, params: { id: video.id } }">
          Join live stream
        </router-link>
      </div>
      <div v-else class="controls subscribe">
        <div>
          <div class="video-level">{{ level | first_letter }}</div>{{ level | capitalize }}
        </div>
      </div>
    </router-link>
  </div>
</template>

<script>

export default {
  name: 'EventTeaser',
  props: {
    video: {
      type: Object,
      required: true,
    },
  },
  computed: {
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
    isOnAir() {
      const dateStart = new Date(this.video.attributes.date.value);
      const dateEnd = new Date(this.video.attributes.date.end_value);
      const now = new Date();
      return dateStart < now && now < dateEnd;
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
  },
};
</script>
