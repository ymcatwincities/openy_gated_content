<template>
  <div class="video-state-indicator">
    <SvgIcon
      v-if="!camEnabled"
      icon="videocam_off_black_24dp"
      class="fill-white"></SvgIcon>
  </div>
</template>

<script>
import SvgIcon from '@/components/SvgIcon.vue';

export default {
  name: 'VideoIndicator',
  components: { SvgIcon },
  props: {
    camEnabled: {
      type: Boolean,
      required: true,
    },
  },
};
</script>
