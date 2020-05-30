<template>
  <div class="video-teaser live-stream-teaser">

      <div class="preview" v-bind:style="{
              backgroundImage: `url(${image})`
            }">
        <div class="month"><p>{{ month }}</p><p>{{ day }}</p></div>
      </div>
      <div class="title">{{ video.attributes.title }}</div>
      <div class="meta">
        <div class="schedule">
          <i class="fa fa-clock-o" aria-hidden="true"></i>
          {{ schedule}}
        </div>
      </div>
      <div v-if="isOnAir" class="controls join">
        <router-link
          :to="{ name: 'LiveStream', params: { id: video.id } }">
          Join live stream
        </router-link>
      </div>
      <div v-else class="controls subscribe">
        <div><div class="video-level">M</div> Moderate</div>
        <div class="fa fa-calendar-plus-o"></div>
      </div>
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
      const vid = this.video.attributes.field_ls_media
        // Use event instance value.
        ? this.video.attributes.field_gc_video_media.field_media_video_id
        // Use parent (series) value.
        : this.video.attributes.media.field_media_video_id;
      // Possible images resolutions here:
      // default.jpg
      // hqdefault.jpg
      // sddefault.jpg
      // maxresdefault.jpg
      return `https://img.youtube.com/vi/${vid}/mqdefault.jpg`;
    },
    schedule() {
      const dateStart = new Date(this.video.attributes.date.value);
      const dateEnd = new Date(this.video.attributes.date.end_value);
      const startHours = dateStart.getHours() % 12 || 12;
      const startMinutes = dateStart.getMinutes();
      const startMorning = dateStart.getHours() < 12 ? 'a.m.' : 'p.m.';
      const endHours = dateEnd.getHours() % 12 || 12;
      const endMinutes = dateEnd.getMinutes();
      const endMorning = dateEnd.getHours() < 12 ? 'a.m.' : 'p.m.';

      let start = `${startHours}:${startMinutes} ${startMorning} - `;
      if (this.isOnAir) {
        start = 'Until ';
      }

      return `${start} ${endHours}:${endMinutes} ${endMorning}`;
    },
    month() {
      const date = new Date(this.video.attributes.date.value);
      const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
      ];

      return monthNames[date.getMonth()];
    },
    day() {
      return new Date(this.video.attributes.date.value).getDate();
    },
    isOnAir() {
      const dateStart = new Date(this.video.attributes.date.value);
      const dateEnd = new Date(this.video.attributes.date.end_value);
      const now = new Date();
      return dateStart < now && now < dateEnd;
    },
  },
  mounted() {
  },
};
</script>

<style lang="scss">
</style>
