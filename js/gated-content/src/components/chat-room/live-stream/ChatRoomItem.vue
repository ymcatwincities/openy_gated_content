<template>
  <div class="control-panel text-white">
    <div class="chat"
         :class="{'unread': unreadMessagesCount}"
         @click="toggleShowChatModal"
    >
      <SvgIcon icon="question_answer_black_24dp"></SvgIcon>
      <span v-if="unreadMessagesCount" class="unread-count">
                  {{ unreadMessagesCount }}
                </span>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import SvgIcon from '@/components/SvgIcon.vue';

export default {
  components: { SvgIcon },
  computed: {
    ...mapGetters([
      'unreadMessagesCount',
    ]),
  },
  methods: {
    ...mapActions([
      'toggleShowChatModal',
    ]),
  },
};
</script>
