<template>
  <div class="meeting-player" :class="view">
    <video
      v-if="partnerMediaStream"
      class="partner"
      :srcObject.prop="partnerMediaStream"
      autoplay="autoplay"
    ></video>
    <video
      v-if="localMediaStream"
      class="local"
      :srcObject.prop="localMediaStream"
      autoplay="autoplay"
      muted="muted"
      :volume.prop="0"
    ></video>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  computed: {
    ...mapGetters([
      'view',
      'localMediaStream',
      'partnerMediaStream',
    ]),
  },
};
</script>
