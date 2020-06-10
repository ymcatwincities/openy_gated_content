<template>
  <div class="live-stream-listing-page">
    <h2 class="gated-container">
      <button v-on:click.stop="backOneDay" class="schedule-dashboard__arrow left" role="button"
              aria-label="previous date"><i class="fa fa-angle-left"></i></button>
      <span class="date" v-cloak>{{ dateFormatted }}</span>
      <button v-on:click.stop="forwardOneDay" class="schedule-dashboard__arrow right"
              role="button" aria-label="next date"><i class="fa fa-angle-right"></i></button>
    </h2>

    <LiveStreamListing class="videos gated-container" :date="date"/>
  </div>
</template>

<script>
import LiveStreamListing from '@/components/LiveStreamListing.vue';

export default {
  name: 'LiveStreamListingPage',
  components: {
    LiveStreamListing,
  },
  data() {
    return {
      date: '',
    };
  },
  computed: {
    dateFormatted() {
      // TODO: change format to "June 12, Friday".
      return this.date.toISOString();
    },
  },
  created() {
    this.date = new Date();
  },
  methods: {
    backOneDay() {
      // TODO: looks like we need to add limit here - not less than today.
      // If we have today date - hide backOneDay button.
      this.date = new Date(this.date.setTime(this.date.getTime() - 86400000));
    },
    forwardOneDay() {
      this.date = new Date(this.date.setTime(this.date.getTime() + 86400000));
    },
  },
};
</script>

<style lang="scss">
</style>
