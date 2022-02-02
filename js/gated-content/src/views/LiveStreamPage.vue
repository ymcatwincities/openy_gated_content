<template>
  <div class="gated-content-video-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="video-wrapper bg-white">
        <div class="video gated-containerV2 px--20-10 pt-40-20">
          <MediaPlayer
            :media="media"
            :autoplay="!!config.components.live_stream.autoplay_videos"
            @playerEvent="logPlaybackEvent($event)"
          />
        </div>
        <ChatRoom></ChatRoom>
      </div>
      <div class="video-footer-wrapper bg-white">
        <div class="video-footer gated-containerV2 px--20-10 py-40-20 text-black">
          <div class="pb-20-10 cachet-book-32-28">{{ video.attributes.title }}</div>
          <div class="video-footer__fav pb-40-20">
            <AddToFavorite
              :id="video.attributes.drupal_internal__id"
              :type="'eventinstance'"
              :bundle="'live_stream'"
              class="rounded-border border-concrete"
            ></AddToFavorite>
            <AddToCalendar :event="event"></AddToCalendar>
            <div class="timer" :class="{live: isOnAir}">
              <template v-if="isOnAir">
                LIVE!
              </template>
              <template v-else>
                Starts in {{ startsIn }}
              </template>
            </div>
            <ChatRoomItem></ChatRoomItem>
          </div>
          <div class="verdana-14-12 text-thunder">
            <div class="video-footer__block">
              <SvgIcon icon="date-icon" class="fill-gray" :growByHeight=false></SvgIcon>
              {{ date }}
            </div>
            <div class="video-footer__block">
              <SvgIcon icon="clock-regular" class="fill-gray" :growByHeight=false></SvgIcon>
              {{ time }} ({{ duration }})
            </div>
            <div class="video-footer__block" v-if="instructors && instructors.length > 0">
              <SvgIcon icon="instructor-icon" class="fill-gray" :growByHeight=false></SvgIcon>
              <ul>
                <li v-for="instructor in instructors" :key="instructor.drupal_internal__tid">
                  <router-link :to="{ name: 'Instructor', params: { id: instructor.uuid }}">
                    {{ instructor.name }}
                  </router-link>
                </li>
              </ul>
            </div>
            <div
              class="video-footer__block"
              v-if="level"
            >
              <SvgIcon icon="difficulty-icon-grey" :css-fill="false"></SvgIcon>
              {{ level | capitalize }}
            </div>
            <div class="video-footer__block video-footer__category"
                 v-if="category && category.length > 0">
              <SvgIcon icon="categories" class="fill-gray" :growByHeight=false></SvgIcon>
              <ul>
                <li
                  v-for="tid in category.map(item => item.drupal_internal__tid)"
                  class="video-footer__category-list-item"
                  :key="tid"
                >
                  <CategoryLinks :tid="tid" />
                </li>
              </ul>
            </div>
            <div
              v-if="video.attributes.equipment.length > 0"
              class="video-footer__block">
              <SvgIcon icon="cubes-solid" class="fill-gray" :growByHeight=false></SvgIcon>
              Equipment:
            </div>
            <ul class="video-footer__equipment">
              <li v-for="equip in video.attributes.equipment"
                  :key="equip.drupal_internal__tid">
                {{ equip.name }}
              </li>
            </ul>
          </div>
          <div
            v-if="description"
            class="verdana-16-14"
            v-html="descriptionProcessed"
          ></div>
        </div>
      </div>
      <EventListing
        :title="config.components.live_stream.up_next_title"
        :excluded-video-id="video.id"
        :viewAll="true"
        :limit="8"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import AddToFavorite from '@/components/AddToFavorite.vue';
import Spinner from '@/components/Spinner.vue';
import MediaPlayer from '@/components/MediaPlayer.vue';
import EventListing from '@/components/event/EventListing.vue';
import AddToCalendar from '@/components/event/AddToCalendar.vue';
import CategoryLinks from '@/components/category/CategoryLinks.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { EventMixin } from '@/mixins/EventMixin';
import { SeriesEventMixin } from '@/mixins/SeriesEventMixin';
import SvgIcon from '@/components/SvgIcon.vue';
import ChatRoom from '@/components/live-chat/modal/ChatRoom.vue';
import ChatRoomItem from '@/components/live-chat/live-stream/ChatRoomItem.vue';

export default {
  name: 'LiveStreamPage',
  mixins: [JsonApiCombineMixin, EventMixin, SeriesEventMixin],
  components: {
    SvgIcon,
    AddToFavorite,
    MediaPlayer,
    EventListing,
    AddToCalendar,
    Spinner,
    CategoryLinks,
    ChatRoom,
    ChatRoomItem,
  },
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      video: null,
      response: null,
      liveChatData: null,
      params: [
        'field_ls_category',
        'field_ls_media',
        'field_ls_level',
        'field_gc_instructor_reference',
        // Data from parent (series).
        'category',
        'media',
        'level',
        'equipment',
        'instructor_reference',
      ],
    };
  },
  computed: {
    // This values most of all from parent (series), but can be overridden by item,
    // so ve need to check this here and use correct value.
    media() {
      return this.video.attributes.field_ls_media ? this.video.attributes.field_ls_media
        : this.video.attributes.media;
    },
    descriptionProcessed() {
      return this.description ? this.description.processed : '';
    },
    event() {
      return {
        start: this.formatDate(this.video.attributes.date.value),
        duration: [this.getDuration(this.video.attributes.date), 'hour'],
        title: this.video.attributes.title,
        description: `Live stream page: ${this.pageUrl}`,
        busy: true,
        guests: [],
      };
    },
  },
  methods: {
    async load() {
      this.loading = true;
      const params = {};
      if (this.params) {
        params.include = this.params.join(',');
      }
      client
        .get('livechat/get-livechat-data')
        .then((response) => {
          this.liveChatData = response.data;
        });

      client
        .get(`jsonapi/eventinstance/live_stream/${this.id}`, { params })
        .then((response) => {
          this.video = this.combine(response.data.data, response.data.included, this.params);
          this.multipleReferencesWorkaround(response);
          this.loading = false;
        }).then(() => {
          this.logPlaybackEvent('entityView');
        }).then(() => {
          this.$store.dispatch('setLiveChatData', {
            liveChatMeetingId: this.id,
            liveChatMeetingDate: this.$dayjs.date(this.video.attributes.date.end_value),
            liveChatLocalName: this.liveChatData.name,
            liveChatUserId: this.liveChatData.user_id,
            liveChatRatchetConfigs: this.liveChatData.ratchet,
          }).then(() => {
            this.$store.dispatch('initRatchetServer');
          });
        })
        .catch((error) => {
          this.error = true;
          this.loading = false;
          console.error(error);
          throw error;
        });
    },
    logPlaybackEvent(eventType) {
      this.$log.trackEvent(eventType, 'series', 'live_stream', this.video.attributes.drupal_internal__id);
    },
  },
};
</script>
