<template>
  <div
    class="join-meeting gated-containerV2 pt-40-20 px--20-10 text-black"
    :class="{shadow: isMeetingComplete || !signalingServerConnected || peerInitializationError}"
  >
    <JoinOptions></JoinOptions>
    <DeviceManager></DeviceManager>
    <div
      v-if="isMeetingComplete"
      class="message cachet-book-24-20"
    >Meeting Complete</div>
    <a
      v-else-if="remoteLink"
      class="indigo-button cachet-book-30-24 text-white px-36-24"
      :href="remoteLink"
    >
      Join meeting
    </a>
    <div
      v-else-if="peerInitializationError"
      class="message cachet-book-24-20"
    >{{ peerInitializationError }}</div>
    <div
      v-else-if="!signalingServerConnected"
      class="message cachet-book-24-20"
    >Connecting...</div>
    <button
      v-else
      class="indigo-button cachet-book-30-24 text-white px-36-24"
      @click="toggleShowUserNameModal"
    >Join meeting</button>

    <UserName></UserName>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import JoinOptions from '@/components/personal-training/modal/JoinOptions.vue';
import DeviceManager from '@/components/personal-training/modal/deviceManager.vue';
import UserName from '@/components/personal-training/modal/UserName.vue';

export default {
  components: { DeviceManager, JoinOptions, UserName },
  computed: {
    ...mapGetters([
      'isMeetingComplete',
      'signalingServerConnected',
      'peerInitializationError',
      'remoteLink',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleShowUserNameModal',
    ]),
  },
};
</script>
