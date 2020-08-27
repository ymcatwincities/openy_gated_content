<template>
  <div class="gated-content-video-page virtual-meeting-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div
        class="virtual-meeting-page__image"
        v-bind:class="{ 'zoom-wrapper': isZoomMeetingLink }"
        v-bind:style="{ backgroundImage: `url(${image})` }"
      >
        <ZoomIframe :src="meetingLink.uri" v-if="isZoomMeetingLink"></ZoomIframe>
        <div class="virtual-meeting-page__link" v-else>
          <a :href="meetingLink.uri" target="_blank" class="btn btn-lg btn-primary">
            {{ meetingLink.title }}
          </a>
        </div>
      </div>
      <div class="video-footer-wrapper">
        <div class="video-footer gated-container">
          <div>
            <div class="video-footer__title">{{ video.attributes.title }}</div>
            <div
              v-if="description"
              class="video-footer__description"
                 v-html="description.processed"
            ></div>
            <AddToCalendar :event="event"></AddToCalendar>
          </div>
          <div>
            <div class="video-footer__block">
              <i class="fa fa-clock-o" aria-hidden="true"></i>
              {{ video.attributes.date.value | month }}
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
            <div class="video-footer__block">
              Category:
              {{ category }}
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
        :title="'UP NEXT'"
        :excluded-video-id="video.id"
        :eventType="'virtual_meeting'"
        :viewAll="true"
        :limit="6"
        :msg="'Virtual Meetings not found.'"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import Spinner from '@/components/Spinner.vue';
import ZoomIframe from '@/components/ZoomIframe.vue';
import EventListing from '@/components/event/EventListing.vue';
import AddToCalendar from '@/components/event/AddToCalendar.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { EventMixin } from '@/mixins/EventMixin';

export default {
  name: 'VirtualMeetingPage',
  mixins: [JsonApiCombineMixin, EventMixin],
  components: {
    EventListing,
    AddToCalendar,
    Spinner,
    ZoomIframe,
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
        'field_ls_level',
        'field_ls_image',
        'field_ls_image.field_media_image',
        // Data from parent (series).
        'category',
        'level',
        'equipment',
        'image',
        'image.field_media_image',
      ],
    };
  },
  computed: {
    // This values most of all from parent (series), but can be overridden by item,
    // so ve need to check this here and use correct value.
    image() {
      if (this.video.attributes['field_ls_image.field_media_image']) {
        return this.video.attributes['field_ls_image.field_media_image'].uri.url;
      }
      if (this.video.attributes['image.field_media_image']) {
        return this.video.attributes['image.field_media_image'].uri.url;
      }
      return null;
    },
    meetingLink() {
      const link = {
        title: 'Join Meeting',
      };
      if (this.video.attributes.field_vm_link) {
        link.uri = this.video.attributes.field_vm_link.uri;
        if (this.video.attributes.field_vm_link.title) {
          link.title = this.video.attributes.field_vm_link.title;
        }
      }
      if (this.video.attributes.meeting_link) {
        link.uri = this.video.attributes.meeting_link.uri;
        if (this.video.attributes.meeting_link.title) {
          link.title = this.video.attributes.meeting_link.title;
        }
      }
      return link;
    },
    isZoomMeetingLink() {
      // Allow regular (zoom.us) or vanity (ymca.zoom.us) URLs.
      const regex = /https:\/\/([a-zA-Z0-9-]*\.)?zoom\.us/;
      console.log('Zoom: ', regex.test(this.meetingLink.uri));
      return this.video && this.meetingLink.uri && regex.test(this.meetingLink.uri);
    },
    event() {
      return {
        start: this.formatDate(this.video.attributes.date.value),
        duration: [this.getDuration(this.video.attributes.date), 'hour'],
        title: this.video.attributes.title,
        description: `${this.description.processed}<br>${this.meetingLink.title}: ${this.meetingLink.uri} <br> Virtual meeting page: ${this.pageUrl}`,
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
        .get(`jsonapi/eventinstance/virtual_meeting/${this.id}`, { params })
        .then((response) => {
          this.video = this.combine(response.data.data, response.data.included, this.params);
          // We need here small hack for equipment.
          // In included we have all referenced items, but in relationship only one.
          // So we need manually pass this items to this.video.attributes.equipment.
          this.video.attributes.equipment = [];
          if (response.data.included.length > 0) {
            response.data.included.forEach((ref) => {
              if (ref.type === 'taxonomy_term--gc_equipment') {
                this.video.attributes.equipment.push(ref.attributes);
              }
            });
          }
          this.loading = false;
        }).then(() => {
          this.$log.trackEventEntityView('series', 'virtual_meeting', this.video.attributes.drupal_internal__id);
        })
        .catch((error) => {
          this.error = true;
          this.loading = false;
          console.error(error);
          throw error;
        });
    },
  },
};
</script>
