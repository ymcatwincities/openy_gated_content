<template>
  <div class="gated-content-video-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="video-wrapper">
        <div class="video gated-container">
          <MediaPlayer
            :media="media"
            @playerEvent="logPlaybackEvent($event)"
          />
        </div>
      </div>
      <div class="video-footer-wrapper">
        <div class="video-footer gated-container">
          <div>
            <div class="video-footer__title">{{ video.attributes.title }}</div>
            <div
              v-if="description"
              class="video-footer__description mb-3"
              v-html="descriptionProcessed"
            ></div>
            <AddToFavorite
              :id="video.attributes.drupal_internal__id"
              :type="'eventinstance'"
              :bundle="'live_stream'"
            ></AddToFavorite>
            <AddToCalendar :event="event" class="mt-3"></AddToCalendar>
          </div>
          <div>
            <div class="video-footer__block">
              <i class="fa fa-clock-o fa-clock" aria-hidden="true"></i>
              {{ video.attributes.date.value | month_short }}
              {{ video.attributes.date.value | day }},
              {{ video.attributes.date | schedule }}
            </div>
            <div class="video-footer__block"
              v-if="level"
            >
              Level: {{ level | capitalize }}
            </div>
            <div class="video-footer__block" v-if="instructor">
              Instructor: {{ instructor }}
            </div>
            <div class="video-footer__block video-footer__category"
                 v-if="category && category.length > 0">
              Category:
              <span v-for="(category_data, index) in category"
                    :key="index">{{ category_data.name }}</span>
            </div>
            <div
              v-if="video.attributes.equipment.length > 0"
              class="video-footer__equipment">
              <i class="fa fa-cubes"></i>
              Equipment:
              <ul>
                <li v-for="equip in video.attributes.equipment"
                    :key="equip.drupal_internal__tid">
                  {{ equip.name }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <EventListing
        :title="config.components.live_stream.up_next_title"
        :excluded-video-id="video.id"
        :viewAll="true"
        :limit="6"
        :msg="'Live streams not found.'"
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
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { EventMixin } from '@/mixins/EventMixin';

export default {
  name: 'LiveStreamPage',
  mixins: [JsonApiCombineMixin, EventMixin],
  components: {
    AddToFavorite,
    MediaPlayer,
    EventListing,
    AddToCalendar,
    Spinner,
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
      params: [
        'field_ls_category',
        'field_ls_media',
        'field_ls_level',
        // Data from parent (series).
        'category',
        'media',
        'level',
        'equipment',
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
        .get(`jsonapi/eventinstance/live_stream/${this.id}`, { params })
        .then((response) => {
          this.video = this.combine(response.data.data, response.data.included, this.params);
          // We need here small hack for equipment.
          // In included we have all referenced items, but in relationship only one.
          // So we need manually pass this items to this.video.attributes.equipment.
          this.video.attributes.equipment = [];
          this.video.attributes.category = [];
          if (response.data.included.length > 0) {
            response.data.included.forEach((ref) => {
              if (ref.type === 'taxonomy_term--gc_equipment') {
                this.video.attributes.equipment.push(ref.attributes);
              }
              if (ref.type === 'taxonomy_term--gc_category') {
                this.video.attributes.category.push(ref.attributes);
              }
            });
          }
          this.loading = false;
        }).then(() => {
          this.logPlaybackEvent('entityView');
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
