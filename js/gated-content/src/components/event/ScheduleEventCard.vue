<template>
  <div class="schedule-event-card" :class="{'past': !isUpcoming}">
    <div class="info">
      <div class="time">{{ event.date | start_and_duration }}</div>
      <div class="title">
        <template v-if="route && isUpcoming">
          <router-link :to="{ name: route, params: { id: event.uuid } }">
            {{ event.title }}
          </router-link>
        </template>
        <template v-else>
          {{ event.title }}
        </template>
      </div>
      <div class="instructor" v-if="event.host_name">{{ event.host_name }}</div>
      <div v-if="isPrivate"><div class="timer private">Private</div></div>
    </div>
    <AddToFavorite
      v-if="isUpcoming"
      :id="event.id"
      :type="event.type"
      :bundle="event.bundle"
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
      const routes = {
        eventinstance: {
          live_stream: 'LiveStream',
          virtual_meeting: 'VirtualMeeting',
        },
        personal_training: {
          personal_training: 'PersonalTraining',
        },
      };
      return routes[this.event.type][this.event.bundle];
    },
    isUpcoming() {
      return (new Date(this.event.date.end_value)).getTime() > (new Date()).getTime();
    },
    isPrivate() {
      return this.event.type === 'personal_training';
    },
  },
};
</script>
