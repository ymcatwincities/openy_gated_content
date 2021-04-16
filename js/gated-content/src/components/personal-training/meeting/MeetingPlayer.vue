<template>
  <div class="meeting-player" :class="view">
    <div class="customer">
      <video
        v-if="customerMediaStream"
        :srcObject.prop="customerMediaStream"
        autoplay="autoplay"
      ></video>
    </div>
    <div class="instructor">
      <video
        v-if="instructorMediaStream"
        :srcObject.prop="instructorMediaStream"
        autoplay="autoplay"
      ></video>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  computed: {
    ...mapGetters([
      'view',
      'instructorMediaStream',
      'customerMediaStream',
    ]),
  },
};
</script>
