<template>
  <div class="meeting-player" :class="view">
    <video
      class="partner"
      :srcObject.prop="partnerMediaStream && remoteVideoState ? partnerMediaStream : ''"
      autoplay="autoplay"
      :class="{
        connected: partnerMediaStream,
        'video-disabled': !remoteVideoState,
      }"
    ></video>
    <video
      class="local"
      :class="{connected: localMediaStream ? 'connected' : ''}"
      :srcObject.prop="localMediaStream ? localMediaStream : null"
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
      'remoteVideoState',
    ]),
  },
};
</script>
