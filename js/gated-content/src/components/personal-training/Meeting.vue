<template>
  <JoinMeeting
    v-if="!isJoinedVideoSession"
  ></JoinMeeting>
  <div
    v-else
    class="personal-training-meeting"
  >
    <Chat></Chat>
    <LeaveMeeting></LeaveMeeting>
    <ViewOptions></ViewOptions>
    <MeetingPlayer></MeetingPlayer>
    <ControlPanel></ControlPanel>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import ControlPanel from '@/components/personal-training/meeting/ControlPanel.vue';
import MeetingPlayer from '@/components/personal-training/meeting/MeetingPlayer.vue';
import JoinMeeting from '@/components/personal-training/meeting/JoinMeeting.vue';
import Chat from '@/components/personal-training/modal/Chat.vue';
import LeaveMeeting from '@/components/personal-training/modal/LeaveMeeting.vue';
import ViewOptions from '@/components/personal-training/modal/ViewOptions.vue';

export default {
  components: {
    Chat, JoinMeeting, MeetingPlayer, ControlPanel, ViewOptions, LeaveMeeting,
  },
  computed: {
    ...mapGetters([
      'isJoinedVideoSession',
    ]),
  },
  watch: {
    isJoinedVideoSession(newVal) {
      if (newVal === true) {
        document.querySelector('html').style.overflow = 'hidden';
      } else {
        document.querySelector('html').style.overflow = 'scroll';
      }
    },
  },
};
</script>
