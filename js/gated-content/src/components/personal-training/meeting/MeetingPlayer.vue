<template>
  <div class="meeting-player" :class="view">
    <video
      class="partner"
      :srcObject.prop="partnerMediaStream ? partnerMediaStream : undefined"
      autoplay="autoplay"
      :class="{connected: partnerMediaStream ? 'connected' : ''}"
    ></video>
    <video
      class="local"
      :class="{connected: localMediaStream ? 'connected' : ''}"
      :srcObject.prop="localMediaStream ? localMediaStream : undefined"
      autoplay="autoplay"
      muted="muted"
      :volume.prop="0"
    ></video>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  // data() {
  //   return {
  //     partnerVideoStyle: {
  //       width: undefined,
  //       background: this.partnerMediaStream ? 'black' : undefined,
  //     },
  //   };
  // },
  // updated() {
  //   // this.partnerVideoStyle.width = this.$el.querySelector('.partner')
  //   .clientHeight * 1.777777778;
  // },
  computed: {
    ...mapGetters([
      'view',
      'localMediaStream',
      'partnerMediaStream',
    ]),
  },
};
</script>
