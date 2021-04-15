<template>
  <div class="join-meeting">
    <JoinOptions></JoinOptions>
    <button
      class="indigo-button cachet-book-30-24 text-white px-36-24"
      @click="toggleShowJoinOptionsModal"
    >Join meeting</button>
  </div>
</template>

<script>
import { mapActions } from 'vuex';
import JoinOptions from '@/components/personal-training/modal/JoinOptions.vue';

export default {
  components: { JoinOptions },
  methods: {
    ...mapActions([
      'toggleShowJoinOptionsModal',
    ]),
  },
};
</script>
