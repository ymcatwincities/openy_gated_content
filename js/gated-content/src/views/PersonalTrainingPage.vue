<template>
  <div class="gated-content-video-page personal-training-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <Meeting></Meeting>
      <div class="video-footer-wrapper bg-white">
        <div class="video-footer gated-containerV2 py-40-20 px--20-10 text-black">
          <div class="pb-20-10 cachet-book-32-28">{{ video.attributes.title }}</div>
          <div class="video-footer__fav pb-40-20">
            <AddToFavorite
              :id="video.attributes.drupal_internal__id"
              :type="'personal_training'"
              :bundle="'personal_training'"
              class="rounded-border border-concrete"
            ></AddToFavorite>
            <AddToCalendar :event="event"></AddToCalendar>
            <div class="timer">
              Private
            </div>
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
            <div class="video-footer__block" v-if="instructor">
              <SvgIcon icon="instructor-icon" class="fill-gray" :growByHeight=false></SvgIcon>
              {{ instructor }}
            </div>
          </div>
          <div class="verdana-16-14">
            <AccordionTab
              v-if="descriptionProcessed"
              class="verdana-16-14"
              title="Description"
              :content="descriptionProcessed"
            >
            </AccordionTab>
            <AccordionTab
              v-if="video.attributes.equipment.length > 0"
              class="verdana-16-14"
              title="Equipment Needed"
            >
              <ul class="video-footer__equipment">
                <li v-for="equip in video.attributes.equipment"
                    :key="equip.drupal_internal__tid">
                  {{ equip.name }}
                </li>
              </ul>
            </AccordionTab>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import client from '@/client';
import AddToFavorite from '@/components/AddToFavorite.vue';
import Spinner from '@/components/Spinner.vue';
import AddToCalendar from '@/components/event/AddToCalendar.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { EventMixin } from '@/mixins/EventMixin';
import SvgIcon from '@/components/SvgIcon.vue';
import Meeting from '@/components/personal-training/Meeting.vue';
import AccordionTab from '@/components/AccordionTab.vue';

export default {
  name: 'PersonalTrainingPage',
  mixins: [JsonApiCombineMixin, EventMixin],
  components: {
    AccordionTab,
    Meeting,
    SvgIcon,
    AddToFavorite,
    AddToCalendar,
    Spinner,
  },
  props: {
    tid: {
      type: String,
      required: true,
    },
  },
  watch: {
    $route: 'load',
  },
  async mounted() {
    await this.load();
  },
  data() {
    return {
      loading: true,
      error: false,
      video: null,
      response: null,
      params: [
        'customer_id',
        'instructor_id',
        'pt_equipment',
      ],
    };
  },
  computed: {
    descriptionProcessed() {
      return this.video.attributes.description ? this.video.attributes.description.processed : '';
    },
    event() {
      return {
        start: this.formatDate(this.video.attributes.date.value),
        duration: [this.getDuration(this.video.attributes.date), 'hour'],
        title: this.video.attributes.title,
        description: `Personal training page: ${this.pageUrl}`,
        busy: true,
        guests: [],
      };
    },
    instructor() {
      return this.video.attributes.instructor_id ? this.video.attributes.instructor_id.display_name : '';
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
        .get(`jsonapi/personal_training/personal_training/${this.tid}`, { params })
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
          this.$log.trackEvent('entityView', 'personal_training', 'personal_training', this.video.attributes.drupal_internal__id);
        }).then(() => {
          let instructorRole = false;
          const instructorId = this.video.attributes.instructor_id.drupal_internal__uid;
          // eslint-disable-next-line no-undef
          if (parseInt(drupalSettings.user.uid, 10) === instructorId) {
            instructorRole = true;
          }
          this.$store.dispatch('initPeer', {
            instructorRole,
            instructorName: this.video.attributes.instructor_id.display_name,
            customerName: this.video.attributes.customer_id.display_name,
            customerPeerId: this.video.attributes.customer_peer_id,
            personalTrainingId: this.video.attributes.drupal_internal__id,
            personalTrainingDate: this.video.attributes.date.end_value,
          });
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
