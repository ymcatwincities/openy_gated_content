import dayjs from 'dayjs';

export const EventMixin = {
  computed: {
    pageUrl() {
      return window.location.href;
    },
    config() {
      return this.$store.getters.getAppSettings;
    },
    date() {
      return this.$dayjs.date(this.video.attributes.date.value).format('dddd, MMMM Do, YYYY');
    },
    time() {
      return this.$dayjs.date(this.video.attributes.date.value).format('h:mm a');
    },
    duration() {
      const min = Math.floor(dayjs.duration(
        this.$dayjs.date(this.video.attributes.date.end_value)
        - this.$dayjs.date(this.video.attributes.date.value),
      ).asMinutes());

      return `${min} ${this.$options.filters.simplePluralize('minute', min)}`;
    },
    startsIn() {
      const eventStartDate = this.$dayjs.date(this.video.attributes.date.value);
      const startsDuration = dayjs.duration(eventStartDate - dayjs());

      if (startsDuration.asHours() >= 48) {
        return `${Math.floor(startsDuration.asDays())} days`;
      }

      const { prependZero } = this.$options.filters;
      return `${prependZero(Math.floor(startsDuration.asHours()))}:${prependZero(startsDuration.minutes())}:${prependZero(startsDuration.seconds())}`;
    },
    isOnAir() {
      const dateStart = new Date(this.video.attributes.date.value);
      const dateEnd = new Date(this.video.attributes.date.end_value);
      const now = new Date();
      return dateStart < now && now < dateEnd;
    },
  },
  methods: {
    formatDate: (date) => {
      if (!date) return '';
      const dateObj = new Date(date);
      return dateObj.toISOString();
    },
    getDuration: (date) => {
      if (!date) return '';
      const dateStart = new Date(date.value);
      const dateEnd = new Date(date.end_value);
      const Diff = Math.abs(dateEnd.getTime() - dateStart.getTime());
      // Diff in hours.
      return Diff / 1000 / 3600;
    },
  },
};
