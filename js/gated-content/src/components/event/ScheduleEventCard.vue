<template>
  <div class="schedule-event-card" :class="{'past': !isUpcoming}">
    <div class="info">
      <div class="time">{{ event.attributes.date | start_and_duration }}</div>
      <div class="title">
        <router-link
          :to="{ name: route, params: { id: event.id } }"
          v-if="isUpcoming"
        >
          {{ event.attributes.title }}
        </router-link>
        <template v-else>
          {{ event.attributes.title }}
        </template>
      </div>
      <div class="instructor" v-if="instructor">{{ instructor }}</div>
    </div>
    <AddToFavorite
      v-if="isUpcoming"
      :id="event.attributes.drupal_internal__id"
      :type="'eventinstance'"
      :bundle="type"
    ></AddToFavorite>
  </div>
</template>

<script>
import AddToFavorite from '@/components/AddToFavorite.vue';

export default {
  name: 'ScheduleEventCard',
  components: {
    AddToFavorite,
  },
  props: {
    event: {
      type: Object,
      required: true,
    },
  },
  computed: {
    route() {
      switch (this.event.type) {
        case 'eventinstance--live_stream':
          return 'LiveStream';
        case 'eventinstance--virtual_meeting':
          return 'VirtualMeeting';
        default:
          return 'LiveStream';
      }
    },
    isUpcoming() {
      return (new Date(this.event.attributes.date.end_value)).getTime() > (new Date()).getTime();
    },
    instructor() {
      return this.event.attributes.field_ls_host_name ? this.event.attributes.field_ls_host_name
        : this.event.attributes.host_name;
    },
    type() {
      return this.event.type.replace('eventinstance--', '');
    },
  },
};
</script>
