<template>
  <div
    class="join-meeting"
    :class="{complete: isMeetingComplete}"
  >
    <JoinOptions></JoinOptions>
    <template v-if="isMeetingComplete">
      <div class="message cachet-book-24-20">Meeting Complete</div>
    </template>
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
    ]),
  },
  methods: {
    ...mapActions([
      'toggleShowJoinOptionsModal',
    ]),
  },
};
</script>
