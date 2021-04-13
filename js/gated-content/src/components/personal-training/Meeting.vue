<template>
  <div class="personal-training-meeting gated-containerV2 pt-40-20 px--20-10 text-black">
    <template
      v-if="isJoinedVideoSession"
    >
      <Chat></Chat>
      <LeaveMeeting></LeaveMeeting>
      <ViewOptions></ViewOptions>
      <MeetingPlayer></MeetingPlayer>
      <ControlPanel></ControlPanel>
    </template>
    <JoinMeeting
      v-else
    ></JoinMeeting>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import ControlPanel from '@/components/personal-training/ControlPanel.vue';
import MeetingPlayer from '@/components/personal-training/MeetingPlayer.vue';
import JoinMeeting from '@/components/personal-training/JoinMeeting.vue';
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
};
</script>
