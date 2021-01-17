<template>
  <div class="gated-content-schedule-page">
    <div v-if="endDate" class="date-filter">
      <button @click.stop="backOneWeek" role="button" :disabled="disablePrevDateButton"
              aria-label="previous date">
        <i class="fa fa-angle-left"></i>
      </button>
      <span class="date" v-cloak>
        {{ startDate | month_short }} {{ startDate | day }} -
        {{ endDate | month_short }} {{ endDate | day }}
      </span>
      <button @click.stop="forwardOneWeek" role="button" aria-label="next date">
        <i class="fa fa-angle-right"></i>
      </button>
    </div>
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <template v-else>
      <div v-if="error">Error loading</div>
      <div v-else class="calendar">
        <div class="mobile">
          <template v-for="(day, index) in listing">
            <div v-if="upcomingEvents(day) > 0" :key="index" class="day"
                 :class="{'collapsed': collapses[index]}">
              <div class="header"
                   @click.stop="collapse(index);">
                <div class="caption">
                  {{ day.date | weekday }},&nbsp;
                  {{ day.date | month }} {{ day.date | day }}
                </div>
                <div class="count">
                  {{ upcomingEvents(day) }}
                  {{ upcomingEvents(day) > 1 ? 'Events' : 'Event' }}
                </div>
                <button role="button" class="day-collapse" aria-label="collapse day">
                  <i class="fa fa-minus" aria-hidden="true"></i>
                </button>
                <button role="button" class="day-expand" aria-label="expand day">
                  <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
              </div>
              <div class="items">
                <template v-for="items in day.hourSlots">
                  <template v-if="typeof items !== 'undefined'">
                    <template v-for="event in items">
                      <ScheduleEventCard v-if="event !== null" :event="event" :key="event.id"/>
                    </template>
                  </template>
                </template>
              </div>
            </div>
          </template>
        </div>
        <div class="desktop">
          <div class="slot dates">
            <div class="hour-row">
              <div v-for="(day, index) in listing" :key="index" class="event-cell date"
                   :class="{'active': currentDay(day.date)}">
                <div class="weekday">{{ day.date | weekday }}</div>
                <div class="date">{{ day.date | month }} {{ day.date | day }}</div>
              </div>
            </div>
          </div>
          <template v-for="(eventsCount, hour) in hours">
            <div v-if="typeof eventsCount !== 'undefined'" class="slot" :key="hour">
              <div class="caption">
                <template v-for="(day, index) in listing">
                  <div class="hour-card" :class="{'active': currentDay(day.date)}" :key="index">
                    <template v-if="outputHours(index)">{{ hour | hour }}</template>
                  </div>
                </template>
              </div>
              <div v-for="slotPlace in eventsCount" :key="slotPlace" class="hour-row">
                <div v-for="(day, index) in listing" :key="index" class="event-cell">
                  <ScheduleEventCard
                    v-if="typeof day.hourSlots[hour] !== 'undefined'
                      && typeof day.hourSlots[hour][slotPlace - 1] !== 'undefined'"
                    :event="day.hourSlots[hour][slotPlace - 1]"/>
                </div>
              </div>
            </div>
          </template>
        </div>
        <div v-if="listingIsEmpty" class="empty-listing">No events scheduled for this week.</div>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios';
import client from '@/client';
import Spinner from '@/components/Spinner.vue';
import ScheduleEventCard from '@/components/event/ScheduleEventCard.vue';

export default {
  name: 'SchedulePage',
  components: {
    Spinner,
    ScheduleEventCard,
  },
  props: {
    msg: {
      String,
      default: 'Events not found.',
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      listing: null,
      hours: null,
      collapses: null,
      startDate: null,
      endDate: null,
      oneDay: 86400 * 1000,
      oneWeek: 7 * 86400 * 1000,
    };
  },
  watch: {
    $route: 'initStartDate',
    startDate: 'load',
  },
  async mounted() {
    this.initStartDate();
  },
  computed: {
    listingIsEmpty() {
      let count = 0;
      this.listing.forEach((day) => {
        day.hourSlots.forEach((slot) => { count += slot.length; });
      });
      return count === 0;
    },
    disablePrevDateButton() {
      const today = new Date();
      return (
        this.startDate.getTime() <= today.getTime()
        && today.getTime() < this.endDate.getTime()
      );
    },
  },
  methods: {
    async load() {
      this.loading = true;
      this.endDate = new Date(this.startDate);
      this.endDate.setTime(this.startDate.getTime() + this.oneWeek - 1);

      const params = {
        filter: {
          dateFilterStart: {
            condition: {
              path: 'date.value',
              operator: '>=',
              value: new Date(
                this.startDate.getFullYear(),
                this.startDate.getMonth(),
                this.startDate.getDate(),
                0,
                0,
                0,
              ),
            },
          },
          dateFilterEnd: {
            condition: {
              path: 'date.value',
              operator: '<',
              value: new Date(
                this.endDate.getFullYear(),
                this.endDate.getMonth(),
                this.endDate.getDate(),
                23,
                59,
                59,
              ),
            },
          },
          status: 1,
        },
        sort: {
          sortByDate: {
            path: 'date.value',
            direction: 'ASC',
          },
        },
      };

      axios
        .all([
          client.get('jsonapi/eventinstance/live_stream', { params }),
          client.get('jsonapi/eventinstance/virtual_meeting', { params }),
        ])
        .then(axios.spread((liveStreamResponse, virtualMeetingResponse) => {
          this.listing = [];
          this.hours = [];
          this.collapses = [];
          for (let i = 0; i < 7; i += 1) {
            this.listing[i] = {
              date: new Date(this.startDate),
              hourSlots: [],
            };
            this.listing[i].date.setTime(this.startDate.getTime() + i * this.oneDay);
            this.collapses[i] = true;
          }
          [
            ...liveStreamResponse.data.data,
            ...virtualMeetingResponse.data.data,
          ].forEach((event) => {
            const start = new Date(event.attributes.date.value);
            const day = start.getDay();
            const hour = start.getHours();
            if (typeof this.listing[day].hourSlots[hour] === 'undefined') {
              this.listing[day].hourSlots[hour] = [];
            }
            this.listing[day].hourSlots[hour].push(event);
            if (typeof this.hours[hour] === 'undefined') {
              this.hours[hour] = [];
            }
            if (typeof this.hours[hour][day] === 'undefined') {
              this.hours[hour][day] = 1;
            } else {
              this.hours[hour][day] += 1;
            }
          });

          this.hours.forEach((eventsCount, hour) => {
            this.hours[hour] = eventsCount.reduce((a, b) => Math.max(a, b));
          });

          let found = false;
          this.listing.forEach((day, index) => {
            if (!found && this.upcomingEvents(day) > 0) {
              this.collapses[index] = false;
              found = true;
            }
          });

          this.loading = false;
        }))
        .catch((error) => {
          this.error = true;
          this.loading = false;
          console.error(error);
        });
    },
    currentDay(date) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return today.getTime() === date.getTime();
    },
    outputHours(index) {
      const sunday = new Date();
      sunday.setHours(0, 0, 0, 0);
      sunday.setDate(sunday.getDate() - sunday.getDay());
      const day = this.listing[index].date;
      return (day.getTime() >= sunday.getTime() + this.oneWeek && index === 3)
        || this.currentDay(day);
    },
    backOneWeek() {
      this.startDate = new Date(this.startDate.setTime(this.startDate.getTime() - this.oneWeek));
      this.updateRoute();
    },
    forwardOneWeek() {
      this.startDate = new Date(this.startDate.setTime(this.startDate.getTime() + this.oneWeek));
      this.updateRoute();
    },
    upcomingEvents(day) {
      let count = 0;
      day.hourSlots.forEach((slot) => {
        slot.forEach((event) => {
          if ((new Date(event.attributes.date.end_value)).getTime() > (new Date()).getTime()) {
            count += 1;
          }
        });
      });
      return count;
    },
    collapse(index) {
      this.collapses[index] = !this.collapses[index];
      this.$forceUpdate();
    },
    initStartDate() {
      const sunday = new Date();
      if (this.$route.query.startDate && this.$route.query.startDate > sunday.getTime()) {
        sunday.setTime(this.$route.query.startDate);
      }
      sunday.setHours(0, 0, 0, 0);
      sunday.setDate(sunday.getDate() - sunday.getDay());
      this.startDate = sunday;
      this.updateRoute();
    },
    updateRoute() {
      const query = {
        ...this.$route.query,
        startDate: this.startDate.getTime(),
      };
      if ((new Date()).getTime() >= this.startDate.getTime()) {
        delete query.startDate;
      }
      if (Object.entries(this.$route.query).toString() !== Object.entries(query).toString()) {
        this.$router.push({ query });
      }
    },
  },
};
</script>
