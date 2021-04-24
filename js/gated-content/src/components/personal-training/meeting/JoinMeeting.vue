<template>
  <div
    class="join-meeting gated-containerV2 pt-40-20 px--20-10 text-black"
    :class="{complete: isMeetingComplete}"
  >
    <JoinOptions></JoinOptions>
    <div
      v-if="isMeetingComplete"
      class="message cachet-book-24-20"
    >Meeting Complete</div>
    <div
      v-else-if="!peerSignalingServerConnected"
      class="message cachet-book-24-20"
    >Connecting...</div>
    <div
      v-else-if="peerInitializationError"
      class="message cachet-book-24-20"
    >{{ peerInitializationError }}</div>
    <button
      v-else
      class="indigo-button cachet-book-30-24 text-white px-36-24"
      @click="toggleShowJoinOptionsModal"
    >Join meeting</button>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import JoinOptions from '@/components/personal-training/modal/JoinOptions.vue';

export default {
  components: { JoinOptions },
  computed: {
    ...mapGetters([
      'isMeetingComplete',
      'peerSignalingServerConnected',
      'peerInitializationError',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleShowJoinOptionsModal',
    ]),
  },
};
</script>
